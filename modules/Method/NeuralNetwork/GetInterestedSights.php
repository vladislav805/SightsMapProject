<?php

	namespace Method\NeuralNetwork;

	use Method\APIPrivateMethod;
	use Model\IController;
	use Model\ListCount;
	use Model\Sight;
	use NeuralNetwork\NeuralNetwork;
	use PDO;

	/**
	 * Получение сети сети.
	 * @package Method\NeuralNetwork
	 */
	class GetInterestedSights extends APIPrivateMethod {

		/** @var boolean */
		protected $forceRebuildNetwork = false;

		/** @var int */
		protected $count = 30;

		/** @var int */
		protected $offset = 0;

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {
			/** @var NeuralNetwork $network */
			// Загружаем нейронную сеть
			// Если её нет, будет создана
			// Если есть, то все веса нейронной сети будут загружены из файла
			$network = $main->perform(new LoadNetwork([
				"forceRebuildNetwork" => $this->forceRebuildNetwork
			]));

			// Выборка достопримечательностей-кандидатов, то есть тех, которые
			// пользователь не посетил или хочет посетить
			// Здесь находятся лишь: sightId, markIds (идентификаторы через
			// запятую), rating
			$candidateSights = $this->getCandidateSights($main);

			$allCount = 0;
			$minW = 0;

			// Прогоняем всех кандидатов через нейронную сеть и вычисляем
			// заинтересованность пользователя в каждом из них
			// Вернутся только {$this->count}
			// Здесь будут только столбца: id и w
			$res = $this->computeWeights($network, $candidateSights, $allCount, $minW);

			// Выбираем все sightId в массив для выборки информации о них
			$sightIds = array_column($res, "id");

			// Сбор информации о достопримечательностях по их ID
			/** @var Sight[] $sights */
			$sights = $main->perform(new \Method\Sight\GetByIds(["sightIds" => $sightIds]));

			// Создание "словаря" для достопримечательностей
			$skv = [];
			foreach ($sights as $sight) {
				$skv[$sight->getId()] = $sight;
			}

			// Пробегаемся по всем результатам от нейронной сети
			// Находим место по ID в "словаре" и заменяем в массиве с
			// результатами элемент на объект достопримечательности
			// Даем объекту информацию о степени интереса
			// Сделано это для того, чтобы второй раз не сортировать массив
			// с объектами, ибо от GetByIds достопримечательности отдаются
			// в порядке увеличения ID, а не в том порядке, который был дан
			// в метод (в нашем случае это по возрастанию степени интереса)
			foreach ($res as &$sight) {
				$w = $sight["w"];

				/** @var Sight $sight */
				$sight = $skv[$sight["id"]];

				//$w = get_relative_of_interval_value_from_interval($w, $minW, 100, 0, 100);

				$sight->setInterest($w);
			}

			unset($sight);

			// Пакуем результат
			$list = new ListCount($allCount, $res);
			$list->putCustomData("error", defined("__NN_ERROR") ? __NN_ERROR : -1);

			return $list;
		}

		/**
		 * Выборка достопримечательностей кандидатов для нахождения интересных
		 *
		 * @param IController $main
		 * @return array[]
		 */
		private function getCandidateSights($main) {
			$stmt = $main->makeRequest("
SELECT
	DISTINCT `p`.`sightId` AS `sightId`, # идентификатор достопримечательности
    GROUP_CONCAT(`markId`) AS `markIds`, # идентификаторы меток через запятую
    IFNULL(`pv`.`state`, 0) AS `state`,  # состояние посещения
    IFNULL(`r`.`rate`, 0) AS `rate`      # рейтинг, который поставил юзер
FROM
	`sight` `p`
		LEFT JOIN `sightVisit` `pv` ON `p`.`sightId` = `pv`.`sightId`
		LEFT JOIN `sightMark`  `pm` ON `p`.`sightId` = `pm`.`sightId`
        LEFT JOIN `rating`     `r`  ON `p`.`sightId` = `r`.`sightId`
WHERE
	`p`.`isArchived` = 0
	AND
	(
		# выборка по посещениям текущего пользователя:
		# не посещал или желает посетить
		(`pv`.`userId` = :userId AND `pv`.`state` = 2)
			OR
		# если не желает, то данных нет и там NULL
		`pv`.`userId` IS NULL
	)
GROUP BY `p`.`sightId`
");

			$stmt->execute([":userId" => $main->getUser()->getId()]);

			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		use TMakeVector;

		/**
		 * @param NeuralNetwork $network
		 * @param array[] $sights
		 * @param int& $count
		 * @param int& $minW
		 * @return null
		 */
		private function computeWeights($network, $sights, &$count, &$minW) {
			$result = [];
			$n = $network->getInputsCount() - 1;

			foreach ($sights as $i => $sight) {
				$rate = (int) $sight["rate"];
				$mIds = $sight["markIds"] === null ? [] : explode(",", $sight["markIds"]);

				$vector = $this->makeTaskVector($mIds, $rate, $n);

				$result[] = [
					"id" => (int) $sight["sightId"],
					"w" => $network->getAnswer($vector)[0]
				];
			}

			usort($result, function($a, $b) {
				return $a["w"] < $b["w"];
			});

			$count = sizeof($result);

			$minW = $result[$count - 1]["w"];

			$result = array_slice($result, $this->offset, $this->count);

			return $result;
		}

	}