<p>Вы можете найти места с помощью поиска по словам, либо с помощью интерактивной карты.</p>
<div class="index-target">
	<div class="index-target-search">
		<a href="/sight/random" class="button"><i class="material-icons">style</i> Случайное место</a>
	</div>
	<div class="index-target-divider"></div>
	<div class="index-target-search">
		<a class="button" href="/map"><i class="material-icons">place</i> Места на карте</a>
	</div>
</div>
<div class="index-target">
	<!--suppress HtmlUnknownTarget -->
	<form class="index-target-search" action="/sight/search" enctype="multipart/form-data">
		<div class="search-wrap-content">
			<div class="fi-wrap">
				<input type="search" name="query" id="m-query" pattern=".+" required="required" />
				<label for="m-query">Название</label>
			</div>
			<input type="submit" value="Поиск" />
		</div>
	</form>
	<div class="index-target-divider"></div>
	<form class="index-target-search" action="#" enctype="multipart/form-data" method="post" id="__index-gotoById">
		<div class="search-wrap-content">
			<div class="fi-wrap">
				<input type="number" name="sightId" id="m-sightId" pattern="\d+" required="required" />
				<label for="m-sightId">Идентификатор sID</label>
			</div>
			<input type="submit" value="Перейти" />
		</div>
	</form>
</div>