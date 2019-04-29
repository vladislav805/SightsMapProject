<?
	/** @var RibbonPage $this */
	/** @var mixed $data */

	use Pages\IncludeRibbonPage;
	use Pages\RibbonPage;

?>
<div class="page-ribbon" id="ribbon-main"<?=!($this instanceof RibbonPage) ? " hidden" : "";?>>
<?
	if ($this instanceof IncludeRibbonPage && ($url = $this->getRibbonIncludeBlock($data)) && file_exists($url)) {
		require_once $url;
	}
?>
	<div class="page-ribbon-image" id="ribbon-image" <? if ($this instanceof RibbonPage && ($ribImage = $this->getRibbonImage($data))) { ?> style="background-image: url('<?=$ribImage;?>');"<? } ?>></div>
	<div class="page-ribbon-inner" id="ribbon-content">
<?
	if ($this instanceof RibbonPage && ($content = $this->getRibbonContent($data))) {
		if (is_string($content)) {
			printf("<h1>%s</h1>", $content);
		} elseif (is_array($content) && inRange(sizeOf($content), 2, 3)) {
			foreach ($content as $i => $str) {
				/** @noinspection PhpFormatFunctionParametersMismatchInspection */
				printf("<h%d>%s</h%1\$1d>", $i + 1, $str);
			}
		}
	}
?>
	</div>
</div>