<!--suppress HtmlFormInputWithoutLabel -->
<html>
	<head>
		<meta charset="utf-8" />
		<title>Sights API Invoker</title>
		<style>

		</style>
		<script>

			function sendRequest(form, event) {
			    event.preventDefault();

			    var method = form.methodName.value.trim(),
				    params = new FormData();

			    Array.prototype.forEach.call(form.querySelectorAll(".__name"), function(item) {
			        if (item.value.trim()) {
			            params.append(item.value.trim(), item.nextElementSibling.value.trim());
			        }
			    });

			    fetch("api.php?method=" + method, {
			        method: "POST",
			        body: params
			    }).then(function(res) { return res.json() }).then(function(res) {
			        setTextResult(JSON.stringify(res, null, "\t"));
			    }).catch(function(re) {
			        console.log(re);
			        setTextResult(re);
			    });



			    return false;
			}

			function setTextResult(res) {
			    document.getElementById("result").innerHTML = res;
			}

		</script>
	</head>
	<body>
		<fieldset>
			<legend>Request</legend>
			<form action="#" onsubmit="return sendRequest(this, event);">
				<label for="methodName">Method</label>
				<input type="text" name="methodName" id="methodName" />
				<p>Params</p>
				<?
					for ($i = 0; $i < 10; ++$i) {
?>
						<div><input type="text" name="name[]" class="__name" /> = <input type="text" name="value[]" class="__value" /></div>
<?
					}

				?>
				<input type="submit" value="Send" />
			</form>
		</fieldset>
		<fieldset>
			<legend>Result</legend>
			<pre id="result"></pre>
		</fieldset>
	</body>
</html>