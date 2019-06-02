<?
	/** @var $this \Pages\NeuralPage */
	/** @var \Method\APIException|null $error */
	/** @var boolean $has */

	use Method\ErrorCode;

	if (!$this->mController->isAuthorized()) {
?>
<div class="neural-heightWideBlock">
	<i class="material-icons neural-heightWideBlock-icon">sentiment_very_dissatisfied</i>
	<h3>Авторизуйтесь, пожалуйста...</h3>
	<p>Данный функционал доступен только авторизованным пользователям.</p>
	<p><button onclick="openLoginForm();">Авторизация</button></p>
</div>
<?
	} else

	if ($error) {
		switch ($error->getCode()) {
			case ErrorCode::NOT_ENOUGH_DATA_FOR_TRAINING:
				$extra = $error->getExtra();
				$now = $extra["now"];
				$required = $extra["required"];
?>
<div class="neural-heightWideBlock" id="neural_error_no_enough_data">
	<i class="material-icons neural-heightWideBlock-icon">sentiment_dissatisfied</i>
	<h3>Упс... Мы плохо знакомы :(</h3>
	<p>... но это можно исправить :)</p>
	<p>Пока что не хватает данных для того, чтобы дать рекомендации (<? printf("есть %d из %d", $now, $required);?>).</p>
	<p>Пожалуйста, поищите еще немного мест, которые Вам могут быть интересны, поставьте лайки/дизлайки..</p>
	<a href="/sight/search">Поиск</a> | <a href="/sight/random">Случайное место</a>
</div>
<?
		}
	} else {
?>
<div class="neural-wrap" id="neural_wrap" data-state="<?=$has ? "learning" : "not_init"?>">
	<div class="neural-list" id="neural_list" data-state="done"></div>

	<div class="neural-heightWideBlock" id="neural_warning_not_inited" data-state="not_init">
		<i class="material-icons neural-heightWideBlock-icon">check</i>
		<h3>Секунду...</h3>
		<p>Нейросеть готова к обучению. Нажмите кнопку ниже, чтобы начать.</p>
		<p>Обучение может длиться около минуты, запаситесь терпением.</p>
		<input type="button" id="neural_btn_init" value="Начать" />
	</div>

	<div class="neural-heightWideBlock" id="neural_info_waiting" data-state="learning">
		<i class="material-icons neural-heightWideBlock-icon">access_time</i>
		<h3>Stay tuned...</h3>
		<p>Пожалуйста, подождите... Это может занять около минуты</p>
		<p>Анализируем Ваши интересы и подбираем места, которые могут быть Вам интересны</p>
	</div>

	<div class="neural-heightWideBlock" id="neural_error_internal" data-state="error">
		<i class="material-icons neural-heightWideBlock-icon">error_outline</i>
		<h3>Упс... Что-то пошло не так</h3>
		<p>Попробуйте еще раз. Если ошибка повторяется постоянно, пожалуйста, попробуйте отметить ещё пару мест посещенными/желаемыми</p>
	</div>
</div>
<?
	}
