<?
	/** @var \Pages\SearchSightPage $this */
	/** @var \Model\ListCount $result */
?>
<h3>Поиск места</h3>
<form action="/sight/search" enctype="multipart/form-data" class="search-form-wrap">
	<div class="search-wrap-content">
		<div class="fi-wrap">
			<input type="search" name="query" id="m-query" pattern=".+" required="required" value="<?=htmlspecialchars($this->query);?>" />
			<label for="m-query">Название</label>
		</div>
		<?=new \UI\StylisedSelect("order", "Сортировка", $this->getOrderVariants())?>
		<input type="hidden" name="cityId" value="<?=(int) $this->cityId;?>" />
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

	if ($this->markId) {
		$hp[] = sprintf("с меткой %s (markId=%d)", $this->mark->getTitle(), $this->mark->getId());
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

			unset($args["id"], $args["name"], $args["r"]);

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