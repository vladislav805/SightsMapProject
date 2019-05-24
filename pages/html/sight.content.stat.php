<?
	/** @var \Model\Sight $info */
	/** @var boolean $isAuth */
	/** @var array $stats */
?>
<div class="sight-statistics">
<?
	$visitStateButton = function($id, $icon, $count, $label) use ($info, $isAuth) {
		$code = "";

		if ($isAuth) {
			$code = "onclick=\"SightPage.setVisitState(this)\"";
		}

		/** @noinspection HtmlUnknownAttribute */
		return sprintf('<button class="button sight-visitState-unit" %s data-pid="%d" data-visit-state="%d">
			<span><i class="material-icons">%s</i> <var>%s</var></span>
			<strong>%s</strong>
		</button>', $code, $info->getId(), $id, $icon, $count, $label);
	};
?>
	<div class="sight-visitState" data-visit-state="<?=$isAuth ? $info->getVisitState() : -1;?>">
		<div class="sight-visitState-line">
<?
	print $visitStateButton(0, "close", "&infin;", "непосещенное");
	print $visitStateButton(1, "check", $stats["visited"], "посещенное");
	print $visitStateButton(2, "directions_run", $stats["desired"], "желаемое");
?>
		</div>
		<button class="button sight-visitState-unit sight-visitState-notInterested" <?=($isAuth ? "onclick=\"SightPage.setVisitState(this)\"" : "");?> data-pid="<?=$info->getId();?>" data-visit-state="3">
			<span><i class="material-icons">not_interested</i> <var><?=$stats["notInterested"];?></var></span>
			<strong>не интересно</strong>
		</button>
	</div>
</div>
