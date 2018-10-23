<p>Вы можете найти места с помощью поиска по словам, либо с помощью интерактивной карты.</p>
<div class="index-target">
	<div class="index-target-search">
		<div class="button" id="__index-button-random"><i class="material-icons">style</i> Случайное место</div>
	</div>
	<div class="index-target-divider"></div>
	<div class="index-target-search">
		<a class="button" href="/map"><i class="material-icons">place</i> Места на карте</a>
	</div>
</div>
<div class="index-target">
	<!--suppress HtmlUnknownTarget -->
	<form class="index-target-search" action="/place/search" enctype="multipart/form-data">
		<div class="search-wrap-content">
			<div class="fi-wrap">
				<input type="search" name="query" id="m-query" pattern=".+" required="required" />
				<label for="m-query">Название</label>
			</div>
			<input type="submit" value="Поиск" />
		</div>
	</form>
	<div class="index-target-divider"></div>
	<form class="index-target-search" action="#" enctype="multipart/form-data" method="post" id="__index-button-map">
		<div class="search-wrap-content">
			<div class="fi-wrap">
				<input type="number" name="pointId" id="m-placeId" pattern="\d+" required="required" />
				<label for="m-placeId">Идентификатор sID</label>
			</div>
			<input type="submit" value="Перейти" />
		</div>
	</form>
</div>