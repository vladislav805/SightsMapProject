<?
	require_once "../modules/tools/PrevailColors.php";

	$url = $_REQUEST["url"];

?>
	<form action="?" method="get">
		<input type="url" autocomplete="off" name="url" value="<?=htmlspecialchars($url)?>" />
		<input type="submit" value="ok">
	</form>
<?



	if ($url) {
		$cls = new \tools\PrevailColors($url);

		$colors = $cls->getColors(0, true, true, 24);

		foreach ($colors as $color => $percent) {
			printf("<div style='background: #%s; height: 30px;'>%.3f%%</div>", $color, $percent);
		}
	}