<?

	namespace Constant;

	final class VisitState {
		const NOT_SPECIFIED = -1;
		const NOT_VISITED = 0;
		const VISITED = 1;
		const DESIRED = 2;
		const NOT_INTERESTED = 3;

		public static function inRange(int $v) {
			return inRange($v, self::NOT_SPECIFIED, self::NOT_INTERESTED);
		}
	}