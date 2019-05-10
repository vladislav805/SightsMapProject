<?

	namespace Method\NeuralNetwork;

	use Method\APIPrivateMethod;
	use Model\IController;
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

			$nn = new \NeuralNetwork\NeuralNetwork([$n, 40, 20, 1]);
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


			if ($nn instanceof \PerceptronNetwork\NeuralNetwork) {
				$nn->setWeightFile("/var/www/vladislav805/data/www/sights.vlad805.ru/w.json", \PerceptronNetwork\NeuralNetwork::WEIGHTS_NOT_READ);
			}

			/*$tasks = [
				[1, 0, 0, 0, 0, 0, 1, 0, 0],
				[1, 1, 0, 0, 0, 0, 0, 0, 0],
				[0, 0, 0, 1, 1, 0, 0, 0, 0],
				[0, 1, 0, 0, 0, 0, 0, 0, 0],
				[1, 0, 0, 1, 1, 0, 0, 0, 0],
				[0, 0, 0, 0, 0, 0, 1, 0, 0],
				[0, 1, 0, 0, 0, 0, 1, 0, 0],
				[1, 1, 0, 1, 1, 0, 0, 0, 0],
			];

			$answers = [
				[1],
				[1],
				[1],
				[1],
				[1],
				[1],
				[1],
				[1]
			];

			list($e, $i) = $nn->trainNeuralNetwork($tasks, $answers, 0.9, 0.2);*/
			/*for ($i = 0; $i < sizeof($tasks); ++$i) {
				print join(" ", $tasks[$i]) . " " . $answers[$i][0] . PHP_EOL;
			}


			exit;*/
			$startLearn = microtime(true);
			$res = $nn->trainNeuralNetwork($tasks, $answers, [
				"learnCoefficient" => 0.9, // 0.9
				"threshold" => 0.4 // 0.2
			]);




			$durLearn = microtime(true) - $startLearn;

			//return $nn;

			$startComputing = microtime(true);


			$ans = array_map(function($item) use ($nn) {
				return $nn->getAnswer($item)[0];
			}, $test);

			//$nn->testNetwork($tasks, $answers);
			return ["grad" => $res["grad"], "answers" => $ans];
			exit;
			$durComputing = microtime(true) - $startComputing;

			return [
				"meta" => [
					"ts" => time(),
					"timeLearning" => $durLearn,
					"timeComputing" => $durComputing,
					"timeExecution" => microtime(true) - $start,
					"error" => $error,
					"iterations" => $iterations
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
					$answers
				)
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
    GROUP_CONCAT(`markId`) AS `markIds`
FROM
	`pointVisit`
		LEFT JOIN `pointMark` ON `pointVisit`.`pointId` = `pointMark`.`pointId`
WHERE
	`pointVisit`.`userId` = :uid
GROUP BY `pointVisit`.`pointId`
SQL;


			$stmt = $main->makeRequest($sql);
			$stmt->execute([":uid" => $main->getUser()->getId()]);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$markVectors = [];
			$stateVector = [];

			$stateValues = [0 => 0, 1 => 1, 2 => 1, 3 => -1];
			//$neg = [1, 0, 0];

			foreach ($result as $item) {
				$ids = $item["markIds"]
					? array_map("intval", explode(",", $item["markIds"]))
					: [];

				$vector = $this->makeTaskVector($ids, $n);
				$markVectors[] = $vector;
				$stateVector[] = [$stateValues[(int) $item["state"]]];

				// negative
				//$markVectors[] = array_map(function($d) use ($neg) { return $neg[$d]; }, $vector);
				//$stateVector[] = [-1];
			}

			return [$markVectors, $stateVector];
		}
	}