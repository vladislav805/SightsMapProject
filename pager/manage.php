<?

	switch (get("do")) {
		case "add":
			require_once "manage-add.php";
			exit;

		case "search":
			require_once "manage-search.php";
			exit;
	}