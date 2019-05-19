<?

	namespace Method\NeuralNetwork;

	use Method\APIPrivateMethod;
	use Model\IController;
	use PDO;

	/**
	 * @package Method\NeuralNetwork
	 */
	class Test extends APIPrivateMethod {


		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			$n = $this->getMarksCount($main);
			$data = $this->getAllUserVisitData($main);
			$w = $this->createInitialWeights($n);

			$d = $this->check($n, $w, [11, 38, 48]);

			return $d;
		}

		/**
		 * @param int $n         Количество меток всего
		 * @param int[][] $w     Веса
		 * @param int[] $markIds Выбранные места у места
		 * @return array
		 */
		private function check($n, $w, $markIds) {
			// Генерация вектора (0, 0, ..., 0)
			$x = array_fill_keys(range(0, $n - 1), 0);

			// Добавление в вектор единиц, метки которых есть у места
			foreach ($markIds as $markId) {
				$x[$markId - 1] = 1;
			}

			$r = [];

			for ($i = 0; $i < $n; ++$i) {
				$r[$i] = [];
				for ($j = 0; $j < $n; ++$j) {
					$r[$i][$j] = $w[$i][$j] * $x[$i];
				}
			}

			$y = [];

			for ($i = 0; $i < $n; ++$i) {
				$y[$i] = array_sum(array_column($r, $i));
			}

			$r = [];

			$beta = 0.1;
			foreach ($y as $k => $e) {

				// Сигмоидальная ф-ция активации (0...1)
				// $sig = 1 / (1 + exp(-$beta * $e));

				// Сигмоидальная ф-ция активации (-1...1)
				$sig = tanh($e / $beta);

				// Какая-то буйда
				// $sig = $e / (1 + abs($e));
				$r[$k] = $sig;
			}


			return $r;
		}

		/**
		 * Создание начальных весовых коэффициентов
		 * @param int $n количество входов
		 * @return int[][]
		 */
		private function createInitialWeights($n) {
			$res = [];
			for ($i = 0; $i < $n; ++$i) {
				$res[$i] = [];
				for ($j = 0; $j < $n; ++$j) {
					$res[$i][$j] = rand(0, 10) / 1000;
				}
			}

			return $res;
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
		 * @return array
		 */
		private function getAllUserVisitData(IController $main) {
			$sql = <<<SQL
SELECT
	`sightVisit`.`sightId` AS `sightId`,
    `sightVisit`.`state` AS `state`,
    GROUP_CONCAT(`markId`) AS `markIds`
FROM
	`sightVisit`
		LEFT JOIN `sightMark` ON `sightVisit`.`sightId` = `sightMark`.`sightId`
WHERE
	`sightVisit`.`userId` = :uid
GROUP BY `sightVisit`.`sightId`
SQL;


			$stmt = $main->makeRequest($sql);
			$stmt->execute([":uid" => $main->getUser()->getId()]);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ($result as &$item) {
				$item = [
					"state" => (int) $item["state"],
					"markIds" => $item["markIds"] ? array_map("intval", explode(",", $item["markIds"])) : []
				];
			}

			return $result;
		}
	}