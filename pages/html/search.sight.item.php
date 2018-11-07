<?
	/** @var \Pages\SearchSightPage $this */
	/** @var \Model\Point $item */
?>
<li class="search-item place-item">
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
		<h5><a href="/place/<?=$item->getId();?>" target="_blank"><?=$this->highlight($item->getTitle());?></a></h5>
<?
	if ($city = $item->getCity()) {
?>
		<p>Город: <a href="/city/<?=$city->getId();?>"><?=htmlSpecialChars($city->getName());?></a></p>
<?
	}

?>
		<p><?=$this->highlight(truncate($item->getDescription(), 240));?></p>
	</div>
</li>