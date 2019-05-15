<?
	/** @var \Pages\NeuralPage $this */
	/** @var \Model\Sight $item */
?>
<li class="search-item place-item <?=$this->getClasses($item);?>">
	<div class="place-item-photo">
<?
	if ($photo = $item->getPhoto()) {
?>
		<div class="place-item-image" style="background-image: url('<?=$photo->getUrlThumbnail();?>');"></div>
<?
	}
?>
	</div>
	<div class="place-item-content">
		<h5><a href="/sight/<?=$item->getId();?>" target="_blank" class="snippet-break-words"><?=htmlSpecialChars($item->getTitle());?></a></h5>
		<p>Индекс интереса: <? printf("%.2f%%", $item->getInterest() * 100); ?></p>
<?
	if ($city = $item->getCity()) {
?>
		<p>Город: <a href="/sight/search?cityId=<?=$city->getId();?>"><?=htmlSpecialChars($city->getName());?></a></p>
<?
	}

	if ($item->getRating()) {
?>
		<p class="search-item-rating"><i class="material-icons"><?=($item->getRating() > 0 ? "thumb_up" : "thumb_down")?></i> <?=$item->getRating();?></p>
<?
	}

?>
		<p class="snippet-break-words"><?=htmlSpecialChars(truncate($item->getDescription(), 240));?></p>
	</div>
</li>