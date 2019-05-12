<?

	namespace Method\NeuralNetwork;

	use Constant\VisitState;
	use Method\APIPrivateMethod;
	use Model\IController;
	use NeuralNetwork\NeuralNetwork;
	use PDO;

	/**
	 * @package Method\NeuralNetwork
	 */
	class Test2 extends APIPrivateMethod {


		const DEBUG = false;

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			$n = self::DEBUG ? 4 : $this->getMarksCount($main);

			$start = microtime(true);

			$path = ROOT_PROJECT . "/assets/test_nn.json";

			$nn = NeuralNetwork::load($path);

			//$nn = new \NeuralNetwork\NeuralNetwork([$n, 15, 10, 1]);
			//$nn = new \PerceptronNetwork\NeuralNetwork($n);

			if (self::DEBUG) {
				$tasks = [
					[1.0, 0.0, 0.0, 0.0],
					[0.0, 1.0 ,0.0, 0.0],
					[1.0, 1.0 ,0.0, 0.0],
					[0.0, 0.0 ,1.0, 0.0],
					[1.0, 0.0 ,1.0, 0.0],
					[0.0, 1.0 ,1.0, 0.0],
					[1.0, 1.0 ,1.0, 0.0],
					[0.0, 0.0 ,0.0, 1.0],
					[1.0, 1.0 ,0.0, 1.0],
					[0.0, 0.0 ,1.0, 1.0],
				];

				$answers = [
					[1.0],
					[0.0],
					[0.0],
					[0.0],
					[0.0],
					[1.0],
					[1.0],
					[1.0],
					[0.0],
					[0.0]
				];

				$test = [
					[1, 0, 0, 1],
					[0, 1, 0, 1]
				];

			} else {
				list($tasks, $answers) = $this->getAllUserVisitData($main, $n);

				$limit = 100;

				if ($limit) {
					$tasks = array_slice($tasks, 0, $limit);
					$answers = array_slice($answers, 0, $limit);
				}

				$test = [
					$this->makeTaskVector([1, 5, 6], $n), // мало
					$this->makeTaskVector([1, 16], $n), // много
					$this->makeTaskVector([1, 10], $n), // много
					$this->makeTaskVector([4, 7], $n), // мало
					$this->makeTaskVector([38], $n), // средне
					$this->makeTaskVector([47], $n), // мало
				];
			}

			/*if ($nn instanceof \PerceptronNetwork\NeuralNetwork) {
				$nn->setWeightFile("/var/www/vladislav805/data/www/sights.vlad805.ru/w.json", \PerceptronNetwork\NeuralNetwork::WEIGHTS_NOT_READ);
			}*/



			$startLearn = microtime(true);
			/*list($error, $iterations) = $nn->trainNeuralNetwork($tasks, $answers, [
				"learnCoefficient" => 0.9, // 0.9
				"threshold" => 0.01 // 0.2
			]);*/

			//$nn->save($path);

			$durLearn = microtime(true) - $startLearn;

			//return $nn;


			$startComputing = microtime(true);
			$ans = array_map(function($item) use ($nn) {
				return $nn->getAnswer($item)[0];
			}, $test);
			$durComputing = microtime(true) - $startComputing;

			return [
				"meta" => [
					"ts" => time(),
					"timeLearning" => $durLearn,
					"timeComputing" => $durComputing,
					"timeExecution" => microtime(true) - $start,
					"error" => -1, //$error,
					"iterations" => -1 //$iterations
				],
				"test" => $ans,
				"input" => array_map(
					function($t, $v) {
						return $t . " => " . $v[0];
					},
					array_map(function($task) {
						$ids = array_keys(array_filter($task, function($v) {
							return $v > 0;
						}));

						$ids = array_map(function($id) {
							return ++$id;
						}, $ids);

						return join(",", $ids);
					}, $tasks),
					$answers)
			];
		}

		private function makeTaskVector($markIds, $n) {
			// Генерация вектора (0, 0, ..., 0)
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
	}