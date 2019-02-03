<?
	/** @var object $data */
	list($item, $arg) = $data;

	switch ($item) {
		case "method":
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
			printf("<dt><code><em>%s</em> %s</code> (%s)</dt><dd>%s</dd>", $param->type, $param->name, $param->required ? "required" : "optional", $this->parseText($param->description));
		}
	} else {
		print "Method not take anyone arguments.";
	}
?>
</ul>
<?
			break;

		case "object":
?>
<h1><?=$arg->name;?></h1>
<h4>Description</h4>
<p><?=$this->parseText($arg->description);?></p>
<h4>Fields</h4>
<ul><?
		foreach ($arg->fields as $field) {
			printf("<dt><code><em>%s</em> %s</code></dt><dd>%s</dd>", $this->parseFormat($field->type), $field->name, $this->parseText($field->description));
		}
?></ul><?
			break;

		default:
?><h4>Methods</h4>
			<ul><?
			$last = "";
			foreach ($arg->methods as $method) {
				$now = $method->category;
				if ($now !== $last) {
					$last = $now;
					printf("<h5>%s</h5>", $last);
				}
				printf("<li><a href='/docs/method/%1\$s'>%1\$s</a></li>", $method->name);
			}
?></ul><h4>Objects</h4>
<ul><?
			foreach ($arg->objects as $o) {
				printf("<li><a href='/docs/object/%1\$s'>%1\$s</a></li>", $o->name);
			}
?></ul><?
	}