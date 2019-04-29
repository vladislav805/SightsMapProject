<?
	/** @var \Pages\BasePage $this */
	/** @var mixed $data */
?>
		</div>
		<div class="page-content-aside">
			ASIDE
		</div>
	</div>
</div>
<footer>
	<div class="footer-left">
		<div class="footer-logo">Sights map</div>
		<ul>
			<li><a href="/">Главная</a></li>
			<li><a href="/map">Карта</a></li>
			<li><a href="/sight/search">Поиск</a></li>
			<li><a href="/docs">API</a></li>
		</ul>
	</div>
	<div class="footer-right">
		<ul>
			<li><a href="//velu.ga/" target="_blank" data-noAjax>velu.ga</a> &copy; 2015&ndash;<?=date("Y");?></li>
		</ul>
	</div>
</footer>
<?
	print $this->pullScripts();

	if ($this->mController->getAuthKey()) {
		?><script>window.API && API.session.setAuthKey(<?=json_encode($this->mController->getAuthKey());?>);</script><?
	}

	if ($js = $this->getJavaScriptInit($data)) {
?>
<script><?=$js;?></script>
<?
	}
