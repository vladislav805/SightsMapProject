<?
	/** @var $this \Pages\NeuralPage */
	/** @var \Model\Sight[] $sights */

	if ($data && $sights) {
?>
<div class="neural-wrap">
	<div class="neural-heightWideBlock" id="neural_info_waiting">
		<i class="material-icons neural-heightWideBlock-icon">access_time</i>
		<h3>Stay tuned...</h3>
		<p>Пожалуйста, подождите... Это может занять около минуты</p>
		<p>Анализируем Ваши интересы и подбираем места, которые могут быть Вам интересны</p>
	</div>
	<div class="neural-heightWideBlock" id="neural_error_no_enough_data">
		<i class="material-icons neural-heightWideBlock-icon">sentiment_dissatisfied</i>
		<h3>Упс... Мы плохо знакомы :(</h3>
		<p>... но это можно исправить :)</p>
		<p>Пока что не хватает данных для того, чтобы дать рекомендации. Пожалуйста, попробуйте еще поставить лайки/дизлайки и посещенные/желаемые места.</p>
	</div>
	<div class="neural-heightWideBlock" id="neural_error_internal">
		<i class="material-icons neural-heightWideBlock-icon">error_outline</i>
		<h3>Упс... Что-то пошло не так</h3>
		<p>Попробуйте еще раз. Если ошибка повторяется постоянно, пожалуйста, попробуйте отметить еще места посещенными/желаемыми</p>
	</div>
</div>
<?
		foreach ($sights as $item) {
			require $this::$ROOT_DOC_DIR . "neural.sight.item.php";
		}
	} else {
?>
<div class="neural-heightWideBlock">
	<i class="material-icons neural-heightWideBlock-icon">sentiment_very_dissatisfied</i>
	<h3>Авторизуйтесь, пожалуйста...</h3>
	<p>Данный функционал доступен только авторизованным пользователям.</p>
</div>
<?
	}
