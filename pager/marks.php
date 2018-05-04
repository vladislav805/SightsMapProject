<?

	use Method\APIException;
	use Model\ListCount;
	use Model\Mark;

	try {
		/** @var ListCount $list */
		$list = $mainController->perform(new Method\Mark\Get([]));
	} /** @noinspection PhpRedundantCatchClauseInspection */ catch (APIException $e) {
		var_dump($e);
		exit;
	}

	$getTitle = function() {
		return "Категории мест | Sights";
	};

	$getOG = function() {
		return [
			"title" => "Категории мест",
			"type" => "article"
		];
	};

	require_once "__header.php";
?>
	<h3>Категории мест</h3>

<?
	/** @var Mark[] $items */
	$items = $list->getItems();

?>
	<div class="mark-list">
		<?
			foreach ($items as $item) {
				?>
				<a class="mark-item" href="mark/<?=$item->getId();?>">
					<div class="mark-item-thumbnail" style="background-color: #<?=getHexColor($item->getColor());?>"></div>
					<div class="mark-content">
						<h5><?=htmlspecialchars($item->getTitle());?></h5>
						<div class="mark-count">N меток</div>
					</div>
				</a>
				<?
			}
		?>
	</div>
<?

	require_once "__footer.php";
