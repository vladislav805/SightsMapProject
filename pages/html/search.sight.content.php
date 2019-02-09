<?
	/** @var \Pages\SearchSightPage $this */
	/** @var \Model\ListCount $result */
?>
<h3>Поиск места</h3>
<form action="/sight/search" enctype="multipart/form-data" class="search-form-wrap" id="search-main-form">
	<div class="search-wrap-content">
		<?=(new UI\StylisedInput("query", "Название", "m-query", $this->query))->setType("search")->setIsRequired(false);?>
		<?=new UI\StylisedSelect("order", "Сортировка", $this->getOrderVariants())?>

		<div class="fi-wrap">
			<div class="fi-handle" data-empty="все" id="searchView_marks" onClick="Search.showMarks(this.form);"><?
	if ($this->marks && sizeOf($this->marks)) {
		print join(", ", array_map(function(\Model\Mark $mark) { return $mark->getTitle(); }, $this->marks));
	}
				?></div>
			<label>Метки</label>
			<input type="hidden" id="search_markIds" name="markIds" value="<?=join(",", $this->markIds);?>" />
		</div>
		<div class="fi-wrap">
			<div class="fi-handle" data-empty="не выбран" id="searchView_city" onClick="Search.showCities(this.form);"><?=$this->city ? $this->city->getName() : "";?></div>
			<label>Город</label>
			<input type="hidden" id="search_cityId" name="cityId" value="<?=(int) $this->cityId;?>" />
		</div>
		<?=new UI\StylisedCheckbox("verified", "только подтвержденные", $this->onlyVerified, 1);?>
		<?=new UI\StylisedCheckbox("archived", "только архивные", $this->onlyArchived, 1);?>
		<?=new UI\StylisedCheckbox("photos", "только с фото", $this->onlyWithPhotos, 1);?>
		<input type="submit" value="Поиск" />
	</div>
</form>
<?
	$hp = [];

	if ($this->query) {
		$hp[] = sprintf("по запросу '%s'", $this->query);
	}

	if ($this->city) {
		$hp[] = sprintf("в городе %s (cityId=%d)", $this->city->getName(), $this->city->getId());
	}

	if ($this->markIds) {
		$hp[] = sprintf("с метками (sizeof(marks)=%d)", sizeof($this->marks));
	}

	if ($this->order) {
		$hp[] = sprintf("сортируя %s (id=%d)", $this->orderKeys[$this->order], $this->order);
	}

?>

<p>Ищем места <?=join(", ", $hp)?></p>
<div class="search-results">
<?
	if (!$this->found) {
?>
	<div class="search-systemMessage">Введите поисковый запрос</div>
<?
	} else {
		if (!$result->getCount()) {
?>
	<div class="search-systemMessage">Ничего не найдено :(</div>
<?
		} else {

			$positionStart = $this->count * $this->page + 1;
			$positionEnd = min($this->count * ($this->page + 1), $result->getCount());

			$paginationString = [];

			$args = $_GET;

			unset($args["id"], $args["action"], $args["r"], $args["_ajax"]);

			if ($this->page >= 1) {
				$args["page"] = $this->page - 1;
				$paginationString[] = "<a href=\"?" . http_build_query($args) . "\" class=\"pagination-item\" data-dir='-'>&laquo; Предыдущая страница</a>";
			}

			if ($positionEnd < $result->getCount()) {
				$args["page"] = $this->page + 1;
				$paginationString[] = "<a href=\"?" . http_build_query($args) . "\" class=\"pagination-item\" data-dir='+'>Следующая страница &raquo;</a>";
			}

			$paginationString = "<div class=\"pagination-wrap\">" . join("", $paginationString) . "</div>";
?>
	<h5>Найдено <?=sprintf("%d %s", $result->getCount(), pluralize($result->getCount(), ["место", "места", "мест"]));?></h5>
	<p>Показаны результаты с <?=$positionStart;?> по <?=$positionEnd;?></p>
	<?=$paginationString;?>
	<ol class="search-items" style="counter-reset: item <?=$positionStart - 1;?>;" start="<?=$positionStart;?>">
<?
			foreach ($result->getItems() as $item) {
				$this->item($item);
			}
?>
	</ol>
<?
			print $paginationString;
		}
	}
	?>
</div>