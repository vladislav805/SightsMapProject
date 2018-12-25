<?
	list($item, $arg) = $data;
	switch ($item) {
		case false:
?><ul><?
			$getCat = function($name) {
				list($category, $name) = explode(".", $name);
				return $category;
			};
			$last = "";
			foreach ($arg as $method) {
				$now = $getCat($method["name"]);
				if ($now !== $last) {
					$last = $now;
					printf("<h5>%s</h5>", $last);
				}
				printf("<li><a href='/docs/%1\$s'>%1\$s</a></li>", $method["name"]);
			}
?></ul><?
			break;

		case true:
?>
<h1><?=$arg["name"];?></h1>
<ul>
<?
	foreach ($arg["params"] as $param) {
		printf("<li><em>%s</em> %s</li>", $param["type"], $param["name"]);
	}
?>
</ul>
<?
	}