<div class="page-ribbon"<?
	if ($ribImage = $this->getRibbonImage($data)) {
?> style="background: url('<?=$ribImage;?>') no-repeat center center; background-size: cover;"<?
	}
?> id="ribbon-main">
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