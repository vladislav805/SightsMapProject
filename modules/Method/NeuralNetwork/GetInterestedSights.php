<?php

	namespace Method\NeuralNetwork;

	use Constant\TypeMovement;
	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Method\Sight\GetByIds;
	use Model\IController;
	use Model\ListCount;
	use Model\Sight;
	use NeuralNetwork\NeuralNetwork;
	use PDO;
	use tools\DBSCAN_Point;
	use tools\DensityBasedSpatialClusteringOfApplicationsWithNoise;

	/**
	 * Получение сети сети.
	 * @package Method\NeuralNetwork
	 */
	class GetInterestedSights extends APIPrivateMethod {

		protected $epsilonMeters4typeMovements = [
			TypeMovement::WALKING => 500,
			TypeMovement::SCOOTER => 600,
			TypeMovement::CYCLING => 900,
			TypeMovement::MOTORCYCLE => 1000
		];

		/** @var boolean */
		protected $forceRebuildNetwork = false;

		/** @var int */
		protected $count = 60;

		/** @var int */
		protected $offset = 0;

		/** @var string */
		protected $typeMovement = null;

		/** @var boolean */
		protected $onlyVerified = false;

		/** @var boolean */
		private $__onlyRoute = false;

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

			// Если указан тип передвижения, то ожидаются уже только маршруты
			if (!empty($this->typeMovement)) {

				// Проверка на то, что передано одно из допустимых значений
				if (!in_array($this->typeMovement, array_keys($this->epsilonMeters4typeMovements))) {
					// В противном случае выкидываем исключение
					throw new APIException(ErrorCode::INVALID_TYPE_MOVEMENT, null, "Unknown type of movement");
				}

				// Помечаем, что ожидается только маршрут
				$this->__onlyRoute = true;

				// Для создания маршрутов понадобится намного больше данных, чем может потребовать пользователь
				$this->count = 200;
			}

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
			$sights = $main->perform(new GetByIds(["sightIds" => $sightIds]));

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

			// Обертка для DBSCAN
			$sights_wrap = array_map(function(Sight $s) {
				return new DBSCAN_Point($s);
			}, $sights);

			// Минимальное количество точек в класере: если маршрут, то 5, иначе - 3
			$minCountPointsInCluster = $this->__onlyRoute ? 5 : 3;

			// Дефолтная длина эпсилон-окрестности - 500 метров
			$epsilonMeters = 500;

			// Если ожидаются только маршруты, берем эпсилон-окрестность конкретного типа передвижения
			if ($this->__onlyRoute) {
				$epsilonMeters = $this->epsilonMeters4typeMovements[$this->typeMovement];
			}


			$ds = new DensityBasedSpatialClusteringOfApplicationsWithNoise($minCountPointsInCluster, $epsilonMeters, $sights_wrap);

			// Кластеризация
			$result_dbscan = $ds->run();

			// Временное хранилище для кластеров
			$_clusters = [];
			foreach ($result_dbscan as $key => $p) {

				// Если текущий элемент шум - пропусаем итерацию
				if (+$p->getClusterId() === DensityBasedSpatialClusteringOfApplicationsWithNoise::NOISE) {

					// а если мы еще собираем только маршруты - то вообще удаляем его из результатов
					if ($this->__onlyRoute) {
						unset($result_dbscan[$key]);
					}
					continue;
				}

				// Если кластер встречается в первый раз, создаем пустой массив для элементов
				if (!isset($_clusters[$p->getClusterId()])) {
					$_clusters[$p->getClusterId()] = [];
				}

				// Добавляем элемент в кластер
				$_clusters[$p->getClusterId()][] = $p->getId();
			}

			// Массив для информации о кластерах
			$clusters = [];

			// Формируем нормальный массив кластеров
			foreach ($_clusters as $id => $cluster) {
				$clusters[] = [
					"id" => (int) $id,
					"items" => $cluster
				];
			}

			// Пакуем результат
			$list = new ListCount($allCount, $res);
			$list->putCustomData("clusters", $clusters);
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
			$onlyVerified = $this->onlyVerified ? "AND `p`.`isVerified` = 1" : "";
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
    {$onlyVerified}
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
		 * Вычисление показателя интереса для мест
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
				// Отзыв пользователя
				$rate = (int) $sight["rate"];

				// Массив из идентификаторов меток
				$mIds = $sight["markIds"] === null ? [] : explode(",", $sight["markIds"]);

				// Вектор входных сигналов
				$vector = $this->makeTaskVector($mIds, $rate, $n);

				// Сохранение результатов: идентификатор и показатель интереса
				$result[] = [
					"id" => (int) $sight["sightId"],
					"w" => $network->getAnswer($vector)[0]
				];
			}

			// Сортировка результатов от наибольшего к меньшему
			usort($result, function($a, $b) {
				return $a["w"] < $b["w"];
			});

			// Количество всех
			$count = sizeof($result);

			// М
			$minW = $result[$count - 1]["w"];

			array_splice($result, $this->count); // offset?

			return $result;
		}

	}