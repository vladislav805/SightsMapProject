<?

	namespace tools;

	use Model\GeoPoint;
	use Model\IGeoPoint;

	class DBSCAN_Point implements IGeoPoint {
		/** @var double */
		private $lat;

		/** @var double */
		private $lng;

		/** @var int */
		private $clusterId = DensityBasedSpatialClusteringOfApplicationsWithNoise::UNCLASSIFIED;

		/** @var GeoPoint */
		private $id;

		public function __construct(GeoPoint $point) {
			$this->id = $point->getId();
			$this->lat = $point->getLat();
			$this->lng = $point->getLng();
		}

		/**
		 * Returns latitude
		 * @return double
		 */
		public function getLat() {
			return $this->lat;
		}

		/**
		 * Returns longitude
		 * @return double
		 */
		public function getLng() {
			return $this->lng;
		}

		/**
		 * @return int
		 */
		public function getClusterId() {
			return $this->clusterId;
		}

		/**
		 * @param int $clusterId
		 * @return DBSCAN_Point
		 */
		public function setClusterId(int $clusterId) {
			$this->clusterId = $clusterId;
			return $this;
		}

		/**
		 * Returns ID of object
		 * @return int
		 */
		public function getId() {
			return $this->id;
		}

		public function jsonSerialize() {
			return null;
		}
	}