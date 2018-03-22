<?

	/** @var MainController $mainController */

	/** @var Model\Mark $info */
	$items = $mainController->perform(new Method\Mark\Get([]))->getItems();


	echo "<pre>";
	var_dump($items);
	echo "</pre>";
