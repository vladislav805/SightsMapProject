<?

	namespace ObjectController;

	use Model\IController;

	abstract class ObjectController {

		/** @var IController */
		protected $mMainController;

		/**
		 * @param IController $mainCtl
		 */
		public final function __construct(IController $mainCtl) {
			$this->mMainController = $mainCtl;
		}

		/**
		 * @return \Model\User|null
		 */
		protected function getCurrentUser() {
			return $this->mMainController->isAuthorized()
				? $this->mMainController->getUser()
				: null;
		}

		/**
		 * @return string
		 */
		protected abstract function getExpectedType();

	}