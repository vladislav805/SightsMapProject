<?php

	namespace Method\NeuralNetwork;

	use Constant\TypeMovement;
	use Method\APIPrivateMethod;
	use Model\IController;

	/**
	 * Получение сети сети.
	 * @package Method\NeuralNetwork
	 */
	class GetParametersForRouting extends APIPrivateMethod {

		public function resolve(IController $main) {
			return [
				"items" => [
					[
						"name" => "typeMovement",
						"label" => "Вид передвижения",
						"type" => "select",
						"variants" => [
							[ "name" => TypeMovement::WALKING, "label" => "пешком, прогулка" ],
							[ "name" => TypeMovement::SCOOTER, "label" => "самокат" ],
							[ "name" => TypeMovement::CYCLING, "label" => "велосипед" ],
							[ "name" => TypeMovement::MOTORCYCLE, "label" => "мотоцикл" ]
						],
						"defaultValue" => "walking"
					],
					[
						"name" => "onlyVerified",
						"label" => "Только подтвержденные",
						"type" => "checkbox",
						"defaultValue" => false
					],
					[
						"name" => "forceRebuildNetwork",
						"label" => "Переобучить нейросеть",
						"type" => "checkbox",
						"defaultValue" => false
					]
				]
			];
		}

	}