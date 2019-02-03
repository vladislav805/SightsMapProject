<h4>Хронология</h4>
<?
	/** @var \Model\Sight $info */
	if ($info->getChild()) {
		$p = $info->getChild();
?>
<p>Раньше здесь была достопримечательность <a href="/sight/<?=$p->getId();?>"><?=$p->getTitle();?></a>.</p>
<?
	}

	if ($info->getParentId()) {
		$p = $info->getParent();
?>
<p>Теперь вместо этой достопримечательности здесь <a href="/sight/<?=$p->getId();?>"><?=$p->getTitle();?></a>.</p>
<?
	}
