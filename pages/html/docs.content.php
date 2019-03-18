<?
	/** @var \Pages\DocsPage $this */
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
				print "<h4>Attention</h4><p>Method require user authorization and specified authKey parameter in request</p>";
			}
?>
<h4>Arguments</h4>
<dl>
<?
	if (sizeOf($arg->params)) {
		foreach ($arg->params as $param) {
			printf("<dt title='%2\$s (%3\$s)'><code><em>%1\$s</em> %2\$s</code> (%3\$s)</dt><dd>%4\$s</dd>", $param->type, $param->name, $param->required ? "required" : "optional", $this->parseText($param->description));
		}
	} else {
		print "Method not take anyone arguments.";
	}
?>
</dl>
<h4>Example</h4>
<form onsubmit="return Docs.runMethod('<?=$arg->name;?>', this, event);">
	<dl>
<?
	if (sizeOf($arg->params)) {
		foreach ($arg->params as $param) {
			$type = "text";
			$step = "";

			if ($param->type === "int" || $param->type === "double") {
				$type = "number";
				$step = $param->type === "int" ? "1" : "any";
			}

			if ($param->type === "boolean") {
				$type = "checkbox\" value=\"1";
			}

			printf('<dt>%1$s%2$s</dt><dd><input type="%3$s" name="%1$s" step="%4$s" /></dd>', $param->name, $param->required ? "*" : "", $type, $step);
		}
	}
	if ($this->mController->isAuthorized()) {
		printf("<input type='hidden' name='authKey' value='%s' />", $this->mController->getAuthKey());
	}
?>
		<input type="submit" value="Run" />
	</dl>
	<pre id="docs-result"></pre>
</form>
<?
			break;

		case "object":
?>
<h1><?=$arg->name;?></h1>
<h4>Description</h4>
<p><?=$this->parseText($arg->description);?></p>
<h4>Fields</h4>
<dl><?
		foreach ($arg->fields as $field) {
			printf("<dt><code><em>%s</em> %s</code></dt><dd>%s</dd>", $this->parseFormat($field->type), $field->name, $this->parseText($field->description));
		}
?></dl><?
			break;


		case "page":
?>
<h1><?=$arg->title;?></h1>
<?
			$this->makePageContent($arg->content);
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