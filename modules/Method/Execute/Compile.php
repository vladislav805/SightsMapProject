<?

	namespace Method\Execute;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Throwable;

	class Compile extends APIPublicMethod {

		/**
		 * Idea for new syntax
		 *
		 * pid=getArg pid;
		 * int $pid;
		 * place=call points.getById -pointId $pid;
		 * comments=call comments.get -placeId $pid;
		 * rat=call rate.get -pointId $pid;
		 * res=new object;
		 * set $res -f place,comments,rating -v $place,$comments,$rat;
		 * ret $res
		 *
		 *
		 * Internal functions:
		 * + getArg [name]
		 * + call [method] -[f1] [v1] ... -[fN] [vN]
		 * + set [variable] -f [name1,...,nameN] -v [value1,...,valueN]
		 * + new [type] 			where [type] = array / object
		 * + ret [variable]
		 * remove [variable]
		 * + int [variable]
		 * throw [errorId]
		 */


		/** @var string */
		protected $code;

		private $dataValues = [];

		private $storage = [];

		/** @var IController */
		private $controller;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {

			$this->controller = $main;

			if (!$this->code) {
				throw new APIException(ErrorCode::NO_PARAM, null, "code is not specified");
			}

			$commands = $this->compile();

			$supportedType = ["set_variable", "command", "return"];

			foreach ($commands as $n => $command) {

				if (!isset($command["type"]) || !in_array($command["type"], $supportedType)) {
					throw new APIException(ErrorCode::RUNTIME_ERROR, null, "Unsupported command");
				}

				switch ($command["type"]) {
					case "set_variable":
						$this->storage["$" . $command["name"]] = $this->compute($command["arguments"]);
						break;

					case "command":
						$this->compute($command);
						break;

					case "return":
						return $this->compute($command["arguments"]);
				}
			}

			return null;
		}

		/**
		 * @return string[][]
		 */
		private function compile() {
			$code = $this->code;
			$code = $this->parseStaticStrings($code);

			/** @var string[] $code */
			$code = array_map("trim", mb_split(";", $code));
			foreach ($code as $n => $line) {

				// Variable
				if (($endName = mb_strpos($line, "=")) !== false) {

					$varName = rtrim(mb_substr($line, 0, $endName));
					$varValue = mb_substr($line, $endName + 1);


					$code[$n] = [
						"type" => "set_variable",
						"name" => $varName,
						"arguments" => $this->parseCommand($varValue)
					];

					continue;
				}

				if (($endName = mb_strpos($line, "ret ")) !== false) {
					$code[$n] = [
						"type" => "return",
						"arguments" => $this->parseCommand(mb_substr($line, $endName))
					];
					continue;
				}

				$code[$n] = $this->parseCommand($line);
			}

			/** @var string[][] $code */
			return $code;
		}

		/**
		 * @param string|array $v
		 * @return array|bool|float|int|string
		 * @throws APIException
		 */
		private function compute($v) {

			if (is_null($v)) {
				return null;
			}

			if (is_bool($v) || is_numeric($v)) {
				return is_bool($v) ? (boolean) $v : (is_int($v) ? (int) $v : (float) $v);
			}

			if (is_string($v)) {
				$tv = trim($v);

				if (mb_strpos($tv, "$") === 0) {
					return $this->storage[$tv];
				}

				if (mb_strpos($tv, "\"") === 0 && mb_strrpos($tv, "\"") === mb_strlen($tv) - 1) {
					return mb_substr($tv, 1, -1);
				}

				return $v;
			}

			if (isset($v["command"])) {

				$stdArg = $v["arguments"][""];

				switch ($v["command"]) {
					case "int":
						return (int) $this->compute($stdArg);

					case "getArg":
						return $_REQUEST[$this->compute($stdArg)];

					case "call":
						global $methods;
						$methodName = $stdArg;

						if (!$methods[$methodName]) {
							return new APIException(ErrorCode::RUNTIME_ERROR, new APIException(ErrorCode::UNKNOWN_METHOD));
						}

						$p = $v["arguments"];

						foreach ($p as $key => $value) {
							$p[$key] = $this->compute($value);
						}
						$res = null;
						try {
							$res = $this->controller->perform(new $methods[$methodName]($p));
						}
						/** @noinspection PhpRedundantCatchClauseInspection */
						catch (APIException $e) {
							$res = $e;
						}
						return $res;
						break;

					case "ret":
						return $this->compute($stdArg);
						break;

					case "rem":
						if (isset($v["arguments"]["k"])) {
							$k = explode(",", $v["arguments"]["k"]);
							foreach ($k as $item) {
								unset($this->storage[$stdArg][$item]);
							}
						} else {
							unset($this->storage[$stdArg]);
						}
						break;

					case "new":
						if (in_array($stdArg, ["object", "array"])) {
							return [];
						} else {
							throw new APIException(ErrorCode::RUNTIME_ERROR, [
								"variables" => $this->storage
							], sprintf("Type '%s' is not supported", $stdArg));
						}
						break;

					case "set":
						if (!isset($this->storage[$stdArg])) {
							throw new APIException(ErrorCode::UNKNOWN_ERROR, [
								"variables" => $this->storage
							], "Variable not defined (" . $stdArg . ")");
						}
						$variable = $this->compute($stdArg);

						$keys = explode(",", $v["arguments"]["f"]);
						$values = explode(",", $v["arguments"]["v"]);

						if (sizeOf($keys) !== sizeOf($values)) {
							throw new APIException(ErrorCode::RUNTIME_ERROR, "set: key list and value list sizes not equals");
						}


						for ($i = 0, $l = sizeOf($keys); $i < $l; ++$i) {
							$variable[$keys[$i]] = $this->compute($values[$i]);
						}

						$this->storage[$stdArg] = $variable;
						break;

					case "throw":
						throw new APIException((int) $stdArg);
						break;

					case "eval":
						$code = str_replace("$", "$ ", $stdArg);
						$res = null;
						try {
							$res = eval(sprintf("return %s;", $code));
						} catch (Throwable $e) {
							$res = NAN;
						}
						return $res;
						break;

					default:
						throw new APIException(ErrorCode::RUNTIME_ERROR, sprintf("Unknown command: %s", $v["command"]));
				}
			}

			return null;
		}

		/**
		 * @param string $code
		 * @return string
		 */
		private function parseStaticStrings($code) {
			$index = -1;
			return preg_replace_callback('/"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/i', function($str) use (&$index) {
				$this->dataValues[++$index] = $str[0];
				return "%%STR_" . $index . "%%";
			}, $code);
		}

		/**
		 * @param string $code
		 * @return string
		 */
		private function restoreStaticStrings($code) {
			return preg_replace_callback("/%%STR_(\d+)%%/im", function($a) {
				return $this->dataValues[(int) $a[1]];
			}, $code);
		}

		private function parseCommand($cmd) {
			$nameSplit = mb_strpos($cmd, " ");

			if ($nameSplit === false) {
				return $this->restoreStaticStrings($cmd);
			}

			$command = mb_substr($cmd, 0, $nameSplit);

			$argsString = mb_substr($cmd, $nameSplit + 1);

			$argsArr = explode(" -", $argsString);

			$args = [];
			foreach ($argsArr as $item) {
				$nameSplit = mb_strpos($item, " ");
				$key = mb_substr($item, 0, $nameSplit);
				$val = trim($this->restoreStaticStrings(mb_substr($item, $nameSplit)));
				$args[$key] = $val;
			}

			return [
				"type" => "command",
				"command" => $command,
				"arguments" => $args
			];
		}


	}