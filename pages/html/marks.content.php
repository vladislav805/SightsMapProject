<h3>Категории мест</h3>
<?
	/** @var \Pages\MarkListPage $this */
	/** @var \Model\Mark[] $items */
?>
<div class="mark-list">
<?
	foreach ($items as $item) {
		$this->item($item);
	}
?>
</div>
