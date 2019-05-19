<?

	namespace Method\Account;

	use Model\User;

	trait TCheckSexRange {

		private static $__availableSexes = [
			User::GENDER_NOT_SET,
			User::GENDER_FEMALE,
			User::GENDER_MALE
		];

		/**
		 * Проерка на то, что передано корректное значение пола
		 * @param string $str Значение
		 * @param boolean $strict Строго ли сравнивать? Если да - NOT_SET не разрешен
		 * @return boolean
		 */
		private function isSexInRange($str, $strict = false) {
			$inRange = in_array($str, self::$__availableSexes);

			return $inRange && ($strict ? $str !== User::GENDER_NOT_SET : true);
		}

	}