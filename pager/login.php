<?

	$login = get("login");
	$password = get("password");

	if ($login && $password) {
		try {
			$res = $mainController->perform(new \Method\Authorize\Authorize(["login" => $login, "password" => $password]));
		} catch (\Method\APIException $e) {
			echo "1";
			exit;
		}

		setCookie(KEY_TOKEN, $res["authKey"], strtotime("+30 days"), "/");
		redirectTo("/user/" . $res["user"]->getLogin());
		exit;
	}

	$getTitle = function() {
		return "Авторизация | Sights";
	};

	$getOG = function() {
		return [
			"title" => "Авторизация",
			"description" => ""
		];
	};

	/** @var MainController $mainController */

	if ($mainController->getSession()) {
		redirectTo("/");
		exit;
	}

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
		<title><?=(isset($getTitle) && is_callable($getTitle) ? $getTitle() : "Sights");?></title>
		<?=(isset($getOG) && is_callable($getOG) ? makeOG($getOG()) : "");?>
		<link rel="stylesheet" href="/css/pages.css" />
		<link rel="stylesheet" href="/css/ui.css" />
	</head>
	<body>
		<div id="head">
			<div id="head-logo">
				<i class="material-icons">&#xe55b;</i>
			</div>

			<div id="head-user">
			</div>
		</div>

		<div class="login-wrap">
			<form action="/login" method="post">
				<div class="login-logo">
					<i class="material-icons">&#xe55b;</i>
					<div class="login-logo-label">Sights Map</div>
				</div>
				<div class="fi-wrap">
					<input type="text" name="login" id="m-login" pattern=".+" required="required" />
					<label for="m-login">Логин или e-mail</label>
				</div>
				<div class="fi-wrap">
					<input type="password" name="password" id="m-password" pattern=".+" required="required" />
					<label for="m-password">Пароль</label>
				</div>
				<div class="login-footer">
					<input value="Вход" type="submit" />
				</div>
			</form>
		</div>

		<footer>
			<div class="footer-right">
				<ul>
					<li><a href="//velu.ga/" target="_blank">velu.ga</a> &copy; 2015&ndash;2018</li>
				</ul>
			</div>
		</footer>
	</body>
</html>