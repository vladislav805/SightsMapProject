<h3>Категории мест</h3>
<?
	/** @var \Pages\MarkListPage $this */
	/** @var \Model\ListCount $data */
	/** @var \Model\Mark[] $items */
	$items = $data->getItems();
?>
<div class="mark-list">
<?
	foreach ($items as $item) {
		$this->item($item);
	}
?>
</div>
