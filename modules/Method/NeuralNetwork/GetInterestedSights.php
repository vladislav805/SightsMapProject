<?php

	namespace Method\NeuralNetwork;

	use Method\APIPrivateMethod;
	use Model\IController;
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
		 * @return array
		 */
		public function resolve(IController $main) {
			/** @var NeuralNetwork $network */
			$network = $main->perform(new LoadNetwork([
				"forceRebuildNetwork" => $this->forceRebuildNetwork
			]));

			$sights = $this->getCandidateSights($main);

			$count = 0;
			$res = $this->computeWeights($network, $sights, $count);

			return [
				"count" => $count,
				"error" => defined("__NN_ERROR") ? __NN_ERROR : -1,
				"items" => $res
			];
		}

		/**
		 * @param IController $main
		 * @return array[]
		 */
		private function getCandidateSights($main) {
			$stmt = $main->makeRequest("
SELECT
	DISTINCT `p`.`pointId` AS `sightId`, # идентификатор достопримечательности
    GROUP_CONCAT(`markId`) AS `markIds`, # идентификаторы меток через запятую
    IFNULL(`pv`.`state`, 0) AS `state`,  # состояние посещения
    IFNULL(`r`.`rate`, 0) AS `rate`,     # рейтинг, который поставил юзер
    `p`.`cityId`                         # город достопримечательности
FROM
	`point` `p`
		LEFT JOIN `pointVisit` `pv` ON `p`.`pointId` = `pv`.`pointId`
		LEFT JOIN `pointMark`  `pm` ON `p`.`pointId` = `pm`.`pointId`
        LEFT JOIN `rating`     `r`  ON `p`.`pointId` = `r`.`pointId`
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
GROUP BY `p`.`pointId`
");

			$stmt->execute([":userId" => $main->getUser()->getId()]);

			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		use TMakeVector;

		/**
		 * @param NeuralNetwork $network
		 * @param array[] $sights
		 * @param int& $count
		 * @return null
		 */
		private function computeWeights($network, $sights, &$count) {
			$result = [];
			$n = $network->getInputsCount() - 1;

			foreach ($sights as $i => $sight) {
				$rate = (int) $sight["rate"];
				$mIds = $sight["markIds"] === null ? [] : explode(",", $sight["markIds"]);

				$vector = $this->makeTaskVector($mIds, $rate, $n);

				$result[] = [
					"id" => (int) $sight["sightId"],
					"w" => $network->getAnswer($vector)[0],
					"rate" => (int) $sight["rate"]
				];
			}

			usort($result, function($a, $b) {
				return $a["w"] < $b["w"];
			});

			$count = sizeof($result);

			$result = array_slice($result, $this->offset, $this->count);

			return $result;
		}

	}