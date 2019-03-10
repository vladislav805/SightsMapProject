<?

	namespace Method\NeuralNetwork;

	use Method\APIPrivateMethod;
	use Model\IController;
	use PDO;

	/**
	 * @package Method\NeuralNetwork
	 */
	class Test2 extends APIPrivateMethod {


		const DEBUG = true;

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			$n = self::DEBUG ? 4 : $this->getMarksCount($main);

			$nn = new \NeuralNetwork\NeuralNetwork($n, [4, 3, 1]);
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

				$limit = 50;

				if ($limit) {
					$tasks = array_slice($tasks, 0, $limit);
					$answers = array_slice($answers, 0, $limit);
				}

				$test = [
					$this->makeTaskVector([1, 5, 6], $n),
					$this->makeTaskVector([1, 10], $n),
					$this->makeTaskVector([4, 7], $n),
					$this->makeTaskVector([47], $n)
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


			list($error, $iterations) = $nn->trainNeuralNetwork($tasks, $answers, [
				"learnCoefficient" => 0.8,
				"sureness" => 0.1
			]);

		/*	$t1 = $nn->getAnswer([1, 0, 0, 1]);
			$t2 = $nn->getAnswer([0, 1, 0, 1]);*/

			return [
				"meta" => [
					"ts" => time(),
					"error" => $error,
					"iterations" => $iterations
				],
				"test" => array_map(function($item) use ($nn) {
					return $nn->getAnswer($item);
				}, $test)
			];
/*
			$m = [
				// 294
				[[4, 5, 16, 24], [0.9]],

				// 600
				[[7, 23, 35, 39], [0.9]],

				// 541
				[[2, 17], [1]],

				// 894
				[[4], [0]],

				// 1600
				[[6, 43], [0]]
			];

			$res = [];

			/**
			 * @var double[] $set
			 * @var double[] $expect
			 * /
			foreach ($m as list($set, $expect)) {
				$res[] = [
					"set" => $set,
					"expect" => $expect[0],
					"result" => $nn->getAnswer($this->makeTaskVector($set, $n))[0]
				];
			}*/

			/*$res = [
				[1, $nn->getAnswer([1, 1, 0, 1, 1, 0, 0, 0, 0])[0]],
				[1, $nn->getAnswer([1, 0, 0, 0, 0, 0, 1, 0, 0])[0]],
				[0, $nn->getAnswer([0, 0, 0, 0, 0, 0, 0, 0, 1])[0]],
				[0, $nn->getAnswer([0, 0, 0, 0, 0, 0, 0, 1, 1])[0]],
			];

			return ["err" => $e, "iterations" => $i, "res" => $res];*/
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

			$stateValues = [0 => 0, 1 => 1, 2 => 1];
//			$neg = [1, 0, 0];

			foreach ($result as $item) {
				$ids = $item["markIds"]
					? array_map("intval", explode(",", $item["markIds"]))
					: [];

				$vector = $this->makeTaskVector($ids, $n);
				$markVectors[] = $vector;
				$stateVector[] = [$stateValues[(int) $item["state"]]];

				// negative
			/*	$markVectors[] = array_map(function($d) use ($neg) { return $neg[$d]; }, $vector);
				$stateVector[] = [0];*/
			}

			return [$markVectors, $stateVector];
		}
	}