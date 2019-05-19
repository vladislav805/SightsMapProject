<?php

	namespace Method\NeuralNetwork;

	use Constant\VisitState;
	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\INotReturnablePublicAPI;
	use NeuralNetwork\NeuralNetwork;
	use PDO;

	/**
	 * Инициализация сети.
	 * @package Method\NeuralNetwork
	 */
	class InitializeWeights extends APIPrivateMethod implements INotReturnablePublicAPI {

		use TGetNetworkWeightsFilePath;
		use TMakeVector;

		/**
		 * Карта слоев и нейронов в них
		 * @var int[]
		 */
		private static $layersMap = [0, 20, 1];

		/**
		 * Максимальный размер обучающей выборки
		 */
		const TRAINING_SET_SIZE_LIMIT = 100;

		private $error;

		/**
		 * Время обучения
		 * @var int
		 */
		private $__debug_training_time;

		private $tasks;

		private $answers;

		private $userData = null;

		/**
		 * @param IController $main
		 * @return NeuralNetwork
		 */
		public function resolve(IController $main) {
			// +1 - рейтинг
			self::$layersMap[0] = ($inputsCount = $this->getMarksCount($main)) + 1;

			$network = new NeuralNetwork(self::$layersMap);

			list($tasks, $answers) = $this->getAllUserVisitAndRatedData($main, $inputsCount);

			$this->tasks = array_slice($tasks, 0, self::TRAINING_SET_SIZE_LIMIT);
			$this->answers = array_slice($answers, 0, self::TRAINING_SET_SIZE_LIMIT);

			$startLearn = microtime(true);

			list($error, $iterations) = $network->trainNeuralNetwork(
				$this->tasks,
				$this->answers,
				[
					"learnCoefficient" => 0.9,
					"threshold" => 0.01
				]
			);

			$this->error = $error;

			$this->__debug_training_time = microtime(true) - $startLearn;

			$network->save($this->getNetworkWeightsFilePath($main));
			return $network;
		}

		/**
		 * @return int
		 */
		public function getTrainingTime() {
			return $this->__debug_training_time;
		}

		/**
		 * @return double
		 */
		public function getError() {
			return $this->error;
		}

		/**
		 * Выборка из БД обучающей выборки
		 * @param IController $main
		 * @return array
		 */
		public function fetchUserData(IController $main) {
			if ($this->userData !== null) {
				return $this->userData;
			}

			$sql = <<<SQL
SELECT
	DISTINCT `sightVisit`.`sightId` AS `sightId`,
    `sightVisit`.`state` AS `state`,
    GROUP_CONCAT(`markId`) AS `markIds`,
    IFNULL(`rating`.`rate`, 0) AS `rate`
FROM
	`sightVisit`
		LEFT JOIN `sightMark` ON `sightVisit`.`sightId` = `sightMark`.`sightId`
        LEFT JOIN `rating` ON `sightVisit`.`sightId` = `rating`.`sightId`
WHERE
	`sightVisit`.`userId` = :uid
GROUP BY `sightVisit`.`sightId`
ORDER BY `rating`.`id` DESC, `sightVisit`.`id` DESC
SQL;


			$stmt = $main->makeRequest($sql);
			$stmt->execute([":uid" => $main->getUser()->getId()]);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (sizeof($result) < NEURAL_NETWORK_LOWER_LIMIT_FOR_START_TRAINING) {
				throw new APIException(ErrorCode::NOT_ENOUGH_DATA_FOR_TRAINING, [
					"now" => sizeof($result),
					"required" => NEURAL_NETWORK_LOWER_LIMIT_FOR_START_TRAINING
				], "No enough data for raining neural network");
			}

			return $this->userData = $result;
		}

		/**
		 * Возвращает массив данных о пользователе: какие места он посещал и какие
		 * хочет, а также идентификаторы меток этих мест в строке через запятую
		 * @param IController $main
		 * @param int $n
		 * @return array
		 */
		private function getAllUserVisitAndRatedData(IController $main, $n) {
			$result = $this->fetchUserData($main);

			$markVectors = [];
			$stateVector = [];

			foreach ($result as $item) {
				$ids = $item["markIds"]
					? array_map("intval", explode(",", $item["markIds"]))
					: [];

				$rate = (int) $item["rate"];

				$vector = $this->makeTaskVector($ids, $rate, $n);
				$markVectors[] = $vector;

				$vs = (int) $item["state"]; // Visit state of user
				$rated = (int) $item["rate"]; // Rate (like/dislike/not spec.) of user
				$output = 0; // Value for output

				// If liked AND already visited OR want to visit
				if ($rated > 0 && $vs === VisitState::VISITED || $vs === VisitState::DESIRED) {
					$output = 1; // full up
				} else

				// If disliked ...
				if ($vs === VisitState::NOT_INTERESTED) {
					// ... and if ...
					$output = $rated < 0
							? -1 // rated down - full down
							: -0.8; // not rated - particular down
				} else

				// If visited ...
				if ($vs === VisitState::VISITED) {
					// ... but if ...
					$output = $rated === 0
							? 0.8 // not rated - maybe it interested (we don't know)
							: -0.1; // rated down (rate up was up) - maybe not interested OR not liked only this sight
				}

				$stateVector[] = [$output];
			}

			return [$markVectors, $stateVector];
		}

		/**
		 * Возвращает количество разновидностей меток для достопримечательностей
		 * @param IController $main
		 * @return int
		 */
		private function getMarksCount(IController $main) {
			$stmt = $main->makeRequest("SELECT COUNT(*) FROM `mark`");
			$stmt->execute();
			list($count) = $stmt->fetch(PDO::FETCH_NUM);
			return (int) $count;
		}
	}