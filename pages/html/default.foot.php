<?
	/** @var \Pages\BasePage $this */
	/** @var mixed $data */
?>
		</div>
	</div>
</div>
<footer>
	<div class="footer-left">
		<div class="footer-logo">Sights map</div>
		<ul>
			<li><a href="/">Главная</a></li>
			<li><a href="/map">Карта</a></li>
			<li><a href="/place/search">Поиск</a></li>
			<li><a href="https://docs.google.com/document/d/18sEUblZnA51Ni_6wAhrqTqCr6if3mkETaEyasnCH1rM/edit?usp=sharing" target="_blank">API</a></li>
		</ul>
	</div>
	<div class="footer-right">
		<ul>
			<li><a href="//velu.ga/" target="_blank">velu.ga</a> &copy; 2015&ndash;2018</li>
		</ul>
	</div>
</footer>
<?
	print $this->pullScripts();

	if ($js = $this->getJavaScriptInit($data)) {
?>
<script><?=$js;?></script>
<?
	}

	if ($this->mController->getAuthKey()) {
?><script>window.API && API.session.setAuthKey(<?=json_encode($this->mController->getAuthKey());?>);</script><?
	}
