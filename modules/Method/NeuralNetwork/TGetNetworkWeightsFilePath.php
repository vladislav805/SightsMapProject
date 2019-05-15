<?
	/**
	 * Created by vlad805.
	 */

	namespace Method\NeuralNetwork;

	use Model\IController;

	trait TGetNetworkWeightsFilePath {

		/**
		 * @param IController $main
		 * @return string
		 */
		public function getNetworkWeightsFilePath(IController $main) {
			return sprintf("%s/userdata/networks/%d.json", ROOT_PROJECT, $main->getUser()->getId());
		}

	}