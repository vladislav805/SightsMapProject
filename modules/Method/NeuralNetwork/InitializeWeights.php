<?php

	namespace Method\NeuralNetwork;

	use Constant\VisitState;
	use Method\APIPrivateMethod;
	use Model\IController;
	use Model\INotReturnablePublicAPI;
	use NeuralNetwork\NeuralNetwork;
	use PDO;

	/**
	 * Инициализация сети.
	 * @package Method\NeuralNetwork
	 */
	class InitializeWeights extends APIPrivateMethod implements INotReturnablePublicAPI {

		/**
		 * Карта слоев и нейронов в них
		 * @var int[]
		 */
		private static $layersMap = [0, 20, 1];

		/**
		 * Максимальный размер обучающей выборки
		 */
		const TRAINING_SET_SIZE_LIMIT = 100;

		/**
		 * Путь для сохранения данных о сети
		 * @var string
		 */
		private $mUserPath;

		/**
		 * Время обучения
		 * @var int
		 */
		private $__debug_training_time;

		/**
		 * @param IController $main
		 * @return NeuralNetwork
		 */
		public function resolve(IController $main) {
			$inputsCount = self::$layersMap[0] = $this->getMarksCount($main);

			$this->mUserPath = sprintf("%s/userdata/networks/%d.json", ROOT_PROJECT, $main->getUser()->getId());

			$network = new NeuralNetwork(self::$layersMap);

			list($tasks, $answers) = $this->getAllUserVisitData($main, $inputsCount);

			$tasks = array_slice($tasks, 0, self::TRAINING_SET_SIZE_LIMIT);
			$answers = array_slice($answers, 0, self::TRAINING_SET_SIZE_LIMIT);

			$startLearn = microtime(true);

			list($error, $iterations) = $network->trainNeuralNetwork($tasks, $answers, [
				"learnCoefficient" => 0.9,
				"threshold" => 0.01
			]);

			$this->__debug_training_time = microtime(true) - $startLearn;

			$network->save($this->mUserPath);
			return $network;
		}

		/**
		 * @return int
		 */
		public function getTrainingTime() {
			return $this->__debug_training_time;
		}

		/**
		 * Возвращает массив данных о пользователе: какие места он посещал и какие
		 * хочет, а также идентификаторы меток этих мест в строке через запятую
		 * @param IController $main
		 * @param int $n
		 * @return array
		 */
		private function getAllUserVisitData(IController $main, $n) {
			$sql = <<<SQL
SELECT
	DISTINCT `pointVisit`.`pointId` AS `sightId`,
    `pointVisit`.`state` AS `state`,
    GROUP_CONCAT(`markId`) AS `markIds`,
    IFNULL(`rating`.`rate`, 0) AS `rate`
FROM
	`pointVisit`
		LEFT JOIN `pointMark` ON `pointVisit`.`pointId` = `pointMark`.`pointId`
        LEFT JOIN `rating` ON `pointVisit`.`pointId` = `rating`.`pointId`
WHERE
	`pointVisit`.`userId` = :uid
GROUP BY `pointVisit`.`pointId`
SQL;


			$stmt = $main->makeRequest($sql);
			$stmt->execute([":uid" => $main->getUser()->getId()]);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$markVectors = [];
			$stateVector = [];

			//$stateValues = [0 => 0, 1 => 1, 2 => 1, 3 => -1];
			//$neg = [1, 0, 0];

			foreach ($result as $item) {
				$ids = $item["markIds"]
					? array_map("intval", explode(",", $item["markIds"]))
					: [];

				$vector = $this->makeTaskVector($ids, $n);
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
		 * Создание из массива идентификаторов вектора из 0 и 1
		 * @param int[] $markIds Идентификаторы мест
		 * @param int $n Количество мест всего
		 * @return int[] Вектор из 0 и 1
		 */
		private function makeTaskVector($markIds, $n) {
			// Генерация пустого вектора (0, 0, ..., 0)
			$x = array_fill(0, $n, 0);

			// Добавление в вектор единиц, метки которых есть у места
			foreach ($markIds as $markId) {
				$x[$markId - 1] = 1;
			}

			return $x;
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