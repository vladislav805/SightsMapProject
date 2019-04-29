<?

	namespace Method\Photo;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Photo;
	use ObjectController\PhotoController;

	class Get extends APIPublicMethod {

		/** @var int|null */
		protected $ownerId = null;

		// OR

		/** @var int|null */
		protected $sightId = null;

		/** @var int */
		protected $count = 30;

		/** @var int */
		protected $offset = 0;

		/**
		 * @param IController $main
		 * @return Photo[]
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			return (new PhotoController($main))->get(
				(int) ($this->ownerId ? $this->ownerId : $this->sightId),
				$this->count,
				$this->offset,
				[ ($this->sightId !== null ? "sight" : "user") => true ]
			);
		}
	}