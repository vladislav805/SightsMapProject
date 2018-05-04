<?

	/** @var MainController $mainController */

	use Model\ListCount;
	use Model\Params;
	use Model\Point;

	/**
	 * @return string
	 */
	$getTitle = function() {
		return "Поиск мест | Sights";
	};

	$getOG = function() {
		return [
			"title" => "Поиск мест",
			//"description" => "",
			"image" => "",
			"type" => "website"
		];
	};

	$currentQuery = trim(get("query"));
	$currentQueryLower = mb_strtolower($currentQuery);
	$pageN = ((int) get("page", 0));

	$COUNT_PER_PAGE = 40;

	$highlight = function($text) use ($currentQueryLower) {
		$src = mb_strtolower($text);
		$res = $text;
		$qLength = mb_strlen($currentQueryLower);
		$last = $qLength;

		while (($pos = mb_strrpos($src, $currentQueryLower, $last)) !== false) {
			$res = mb_substr($res, 0, $pos) . "<ins>" . mb_substr($res, $pos, $qLength) . "</ins>" . mb_substr($res, $pos + $qLength);
			$last = $pos + 1;
		}

		return $res;
	};

	require_once "__header.php";
?>
	<h3>Поиск места</h3>

	<!--suppress HtmlUnknownTarget -->
	<form action="/place/search" enctype="multipart/form-data" class="search-form-wrap">

		<div class="search-wrap-content">
			<div class="fi-wrap">
				<input type="search" name="query" id="m-query" pattern=".+" required="required" value="<?=htmlspecialchars($currentQuery);?>" />
				<label for="m-query">Название</label>
			</div>
			<input type="submit" value="Поиск" />
		</div>
	</form>

	<div class="search-results">
<?
	if ($currentQuery) {
		$params = new Params;
		$params->set("query", $currentQuery)
			->set("offset", $COUNT_PER_PAGE * $pageN)
			->set("count", $COUNT_PER_PAGE);

		/** @var ListCount $result */
		$result = $mainController->perform(new \Method\Point\Search($params));

		if ($result->getCount()) {

			$positionStart = $COUNT_PER_PAGE * $pageN + 1;
			$positionEnd = min($COUNT_PER_PAGE * ($pageN + 1), $result->getCount());

			$paginationString = [];

			$args = $_GET;

			unset($args["r"], $args["do"], $args["arg"]);

			if ($pageN >= 1) {
				$args["page"] = $pageN - 1;
				$paginationString[] = "<a href=\"?" . http_build_query($args) . "\" class=\"pagination-item\">&laquo; Предыдущая страница</a>";
			}

			if ($positionEnd < $result->getCount()) {
				$args["page"] = $pageN + 1;
				$paginationString[] = "<a href=\"?" . http_build_query($args) . "\" class=\"pagination-item\">Следующая страница &raquo;</a>";
			}

			$paginationString = "<div class=\"pagination-wrap\">" . join("", $paginationString) . "</div>";
?>
			<h5>Найдено <?=$result->getCount() . " " . pluralize($result->getCount(), "место", "места", "мест");?></h5>
			<p>Показаны результаты с <?=$positionStart;?> по <?=$positionEnd;?></p>
			<?=$paginationString;?>
			<ol class="search-items" start="<?=$positionStart;?>">
<?
			/** @var Point $item */
			foreach ($result->getItems() as $item) {
?>
				<li class="search-item">
					<h5><a href="<?=getHumanizeURLPlace($item);?>" target="_blank"><?=$highlight(htmlspecialchars($item->getTitle()));?></a></h5>
					<p><?=$highlight(htmlspecialchars(truncate($item->getDescription(), 240)));?></p>
				</li>
<?
			}
?>
			</ol>
			<?=$paginationString;?>
<?
		} else {
?>
			<div class="search-systemMessage">Ничего не найдено :(</div>
<?

		}
	} else {
?>
		<div class="search-systemMessage">Введите поисковый запрос</div>
<?
	}
?>
	</div>

	<script src="/js-pager/utils.js"></script>
	<script src="/js-pager/manage.js"></script>
	<script src="/lib/sugar.min.js"></script>
<?
	require_once "__footer.php";