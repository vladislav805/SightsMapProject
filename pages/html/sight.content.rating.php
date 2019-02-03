<?
	/** @var \Model\Sight $info */
?>
<div class="sight-rating" data-user="<?=$info->getRated();?>">
	<button class="button material-icons sight-rating--setter" data-value="down" onclick="Sight.setRating(this, -1)" data-pid="<?=$info->getId();?>">thumb_down</button>
	<strong><?=$info->getRating();?></strong>
	<button class="button material-icons sight-rating--setter"  data-value="up" onclick="Sight.setRating(this, 1)" data-pid="<?=$info->getId();?>">thumb_up</button>
</div>
