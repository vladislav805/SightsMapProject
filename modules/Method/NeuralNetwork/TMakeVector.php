<?

	namespace Method\NeuralNetwork;

	trait TMakeVector {
		/**
		 * Создание из массива идентификаторов вектора из 0 и 1
		 * @param int[] $markIds Идентификаторы мест
		 * @param int $rating Ретинг места у пользователя (-1, 0, 1)
		 * @param int $n Количество мест всего
		 * @return double[] Вектор из 0 и 1
		 */
		private function makeTaskVector($markIds, $rating, $n) {
			// Генерация пустого вектора (0, 0, ..., 0)
			$x = array_fill(0, $n + 1, 0);

			// Добавление в вектор единиц, метки которых есть у места
			foreach ($markIds as $markId) {
				$x[$markId - 1] = 1;
			}

			$x[$n] = $rating;

			return $x;
		}
	}