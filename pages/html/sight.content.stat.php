<?
	/** @var \Model\Sight $info */
	/** @var boolean $isAuth */
	/** @var array $stats */
?>
<div class="sight-statistics">
	<!--h5>Статистика</h5-->
<?
	$visitStateButton = function($id, $icon, $count, $label) use ($info, $isAuth) {
		$code = "";

		if ($isAuth) {
			$code = "onclick=\"Sight.setVisitState(this)\"";
		}

		/** @noinspection HtmlUnknownAttribute */
		return sprintf('<button class="button sight-visitState-unit" %s data-pid="%d" data-visit-state="%d">
			<span><i class="material-icons">%s</i> <var>%s</var></span>
			<strong>%s</strong>
		</button>', $code, $info->getId(), $id, $icon, $count, $label);
	};
?>
	<div class="sight-visitState" data-visit-state="<?=$isAuth ? $info->getVisitState() : -1;?>">
<?
	print $visitStateButton(0, "close", "&infin;", "непосещенное");
	print $visitStateButton(1, "check", $stats["visited"], "посещенное");
	print $visitStateButton(2, "directions_run", $stats["desired"], "желаемое");
?>
	</div>
</div>
