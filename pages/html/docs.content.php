<?
	list($item, $arg) = $data;
	switch ($item) {
		case false:
?><ul><?
			$last = "";
			foreach ($arg as $method) {
				$now = $method->category;
				if ($now !== $last) {
					$last = $now;
					printf("<h5>%s</h5>", $last);
				}
				printf("<li><a href='/docs/%1\$s'>%1\$s</a></li>", $method->name);
			}
?></ul><?
			break;

		case true:
?>
<h1><?=$arg->name;?></h1>
<h4>Description</h4>
<p><?=$this->parseText($arg->description);?></p>
<?
			if ($arg->onlyAuthorized) {
				print "<h4>Attention</h4><p>Method required authorization and specified authKey parameter</p>";
			}
?>
<h4>Arguments</h4>
<ul>
<?
	if (sizeOf($arg->params)) {
		foreach ($arg->params as $param) {
			printf("<li><code><em>%s</em> %s</code> (%s) &mdash; %s</li>", $param->type, $param->name, $param->required ? "required" : "optional", $this->parseText($param->description));
		}
	} else {
		print "Method not take anyone arguments.";
	}
?>
</ul>
<?
	}