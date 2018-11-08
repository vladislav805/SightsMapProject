<?
	/** @var \Model\Mark $item */
?>
<a class="mark-item" href="/place/search?markIds=<?=$item->getId();?>">
	<div class="mark-item-thumbnail" style="background-color: #<?=getHexColor($item->getColor());?>"></div>
	<div class="mark-content">
		<h5><?=htmlspecialchars($item->getTitle());?></h5>
		<div class="mark-count">N меток</div>
	</div>
</a>