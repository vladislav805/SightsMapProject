<?

	interface INeuralNetwork {

		/**
		 * Подача нейронной сети обучающей выборки и правильных ответов к ней
		 * @param double[][]|int[][]  $task     Обучающая выборка
		 * @param double[][]|int[][]  $answers  Правильные ответы
		 * @param array               $options  Параметры для обучения
		 * @return array
		 */
		public function trainNeuralNetwork($task, $answers, $options = []);

		/**
		 * Получение ответа по заданным входным параметрам
		 * @param double[]|int[] $task  Выборка
		 * @return double[]
		 */
		public function getAnswer($task);

	}