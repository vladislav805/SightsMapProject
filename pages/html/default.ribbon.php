<div class="page-ribbon" id="ribbon-main">
<?
	if ($ribImage = $this->getRibbonImage($data)) {
?>
	<div class="page-ribbon-image" style="background: url('<?=$ribImage;?>') no-repeat center center; background-size: cover;"></div>
<?
	}
?>
	<div class="page-ribbon-inner">
<?
	if ($content = $this->getRibbonContent($data)) {
		if (is_string($content)) {
			printf("<h1>%s</h1>", $content);
		} elseif (is_array($content) && inRange(sizeOf($content), 2, 3)) {
			foreach ($content as $i => $str) {
				printf("<h%d>%s</h%1\$1d>", $i + 1, $str);
			}
		}
	}
?>
	</div>
</div>