<?

	namespace tools;

	/**
	 * Алгоритм кластеризации DBSCAN
	 * Class DensityBasedSpatialClusteringOfApplicationsWithNoise
	 * @package tools
	 */
	class DensityBasedSpatialClusteringOfApplicationsWithNoise {

		const UNCLASSIFIED = -1;
		const NOISE = -2;

		/** @var DBSCAN_Point[] */
		private $mPoints;

		/** @var int */
		private $mMinPoints;

		/** @var double */
		private $mEpsilon;

		/**
		 * DBSCAN constructor.
		 * @param int $min_pts
		 * @param double $eps
		 * @param DBSCAN_Point[] $points
		 */
		public function __construct(int $min_pts, float $eps, array $points) {
			$this->mMinPoints = $min_pts;
			$this->mEpsilon = $eps;
			$this->mPoints = $points;
		}

		/**
		 * @return DBSCAN_Point[]
		 */
		public function run() {
			$clusterId = 1; // первый кластер

			// Пробегаясь по всем точкам
			foreach ($this->mPoints as $point) {
				// Если текущая точка еще не в кластере
				if ($point->getClusterId() === self::UNCLASSIFIED) {
					//
					if ($this->expandCluster($point, $clusterId)) {
						$clusterId += 1;
					}
				}
			}
			return $this->mPoints;
		}

		/**
		 * Расчет расстояний от точки $point
		 * @param DBSCAN_Point $point Точка от которой производится расчет
		 * @return int[] Массив с индексами точек, которые находятся близко
		 */
		public function calculateCluster(DBSCAN_Point $point) {
			$index = 0;
			$clusterIndex = [];

			foreach ($this->mPoints as $item) {
				// Если расстояние от $point до текущей точки меньше, чем epsilon
				if (get_distance($point, $item) <= $this->mEpsilon) {
					// Добавляем индекс
					$clusterIndex[] = $index;
				}

				++$index;
			}
			return $clusterIndex;
		}

		/**
		 *
		 * @param DBSCAN_Point $point
		 * @param int $clusterId
		 * @return boolean
		 */
		public function expandCluster(DBSCAN_Point &$point, int $clusterId) {
			$clusterSeeds = $this->calculateCluster($point);

			// Если количество точек в кластере меньше, чем minPoints
			if (sizeof($clusterSeeds) < $this->mMinPoints) {

				// То эта точка не имеет близких соседей - шум
				$point->setClusterId(self::NOISE);

				return false;
			}

			$indexNow = 0;
			$indexCorePoint = 0;

			// По всем индексам, которые рядом
			foreach ($clusterSeeds as $index) {
				$curr = $this->mPoints[$index];

				// Изменяем у текущего номер кластера
				$curr->setClusterId($clusterId);

				if ($curr->getLng() === $point->getLng() && $curr->getLat() === $point->getLat()) {
					$indexCorePoint = $indexNow;
				}

				++$indexNow;
			}

			// Удаляем первые $indexCorePoint индексов
			array_splice($clusterSeeds, 0, $indexCorePoint);

			// Пробегаемся по последним
			for ($i = 0, $l = sizeof($clusterSeeds); $i < $l; ++$i) {

				// Вычисляем индексы соседей i-го соседа
				$clusterNeighbors = $this->calculateCluster($this->mPoints[$clusterSeeds[$i]]);

				// Если количество соседей соседа больше или равно minPoints
				if (sizeof($clusterNeighbors) >= $this->mMinPoints) {

					// Пробегаемся по соседям соседей
					foreach ($clusterNeighbors as $neighbor) {
						$curCluster = $this->mPoints[$neighbor]->getClusterId();

						// Если сосед соседа не отнесен ни к одному кластеру или является шумом
						if ($curCluster === self::UNCLASSIFIED || $curCluster === self::NOISE) {

							// ... а если не отнесен, но не шум
							if ($curCluster === self::UNCLASSIFIED) {
								// Добавляем его к соседям, которых мы будем осматривать позже
								$clusterSeeds[] = $neighbor;

								// Обновляем количество итераций в цикле
								$l = sizeof($clusterSeeds);
							}

							// ... меняем номер кластера для соседа соседа
							$this->mPoints[$neighbor]->setClusterId($clusterId);
						}
					}
				}
			}

			return true;
		}

		/**
		 * Возвращает массив с кластерами
		 * @return DBSCAN_Point[]
		 */
		public function getPoints() {
			return $this->mPoints;
		}

	}

