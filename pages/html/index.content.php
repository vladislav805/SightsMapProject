<p>Приветствую на нашем сайте!</p>
<p>Здесь Вы можете найти места с помощью поиска по словам, либо с помощью интерактивной карты.</p>
<p>После того, как Вы отметите интересующие Вас места или категории мест, наша <s>армия миньонов</s> нейронная сеть попытается предложить Вам маршрут по интересным достопримечательностям на ближайшие выходные :)</p>
<div class="index-target">
	<div class="index-target-search">
		<a href="/sight/random" class="button index-target-link">
			<div class="material-icons">style</div>
			<span>Случайное место</span>
		</a>
	</div>
	<div class="index-target-divider"></div>
	<div class="index-target-search">
		<a href="/map" class="button index-target-link">
			<div class="material-icons">place</div>
			<span>Места на карте</span>
		</a>
	</div>
</div>
<div class="index-target index-forms">
	<!--suppress HtmlUnknownTarget -->
	<form class="index-target-search" action="/sight/search" enctype="multipart/form-data">
		<div class="search-wrap-content">
			<?=new UI\StylisedInput("query", "Поиск по названию", "m-query");?>
			<input type="submit" value="Поиск" />
		</div>
	</form>
	<div class="index-target-divider"></div>
	<form class="index-target-search" action="#" enctype="multipart/form-data" method="post" id="__index-gotoById">
		<div class="search-wrap-content">
			<div class="fi-wrap">
				<input type="number" name="sightId" id="m-sightId" pattern="\d+" required="required" />
				<label for="m-sightId">Перейти по идентификатору sID</label>
			</div>
			<input type="submit" value="Перейти" />
		</div>
	</form>
</div>