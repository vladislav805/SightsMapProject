<?
	/** @var \Model\Mark $item */
?>
<a class="mark-item" href="/sight/search?markIds=<?=$item->getId();?>">
	<div class="mark-item-thumbnail" style="background-color: #<?=getHexColor($item->getColor());?>"></div>
	<div class="mark-content">
		<h5><?=htmlSpecialChars($item->getTitle());?></h5>
		<div class="mark-count"><?=$item->getCount();?> <?=pluralize($item->getCount(), "место", "места", "мест");?></div>
	</div>
</a>