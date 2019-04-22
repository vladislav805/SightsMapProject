<?

	use Model\City;
	use Model\ListCount;

	/** @var ListCount $data */

	list($data) = $data;

	if (!($data instanceof ListCount)) {
		exit;
	}

	/** @var City[] $items */
	$items = $data->getItems();

	$assoc = [];

	foreach ($items as $city) {
		$assoc[$city->getId()] = $city;
	}
?>
<div class="spoiler">
	<div class="spoiler-head">Добавить город</div>
	<form class="spoiler-content" action="#" method="post" id="__adminCityAdd">
		<?=(new UI\StylisedInput("name", "Название"));?>
		<?=(new UI\StylisedSelect("parentId", "Дочернее место", Utils\generateCitiesTree($items)));?>
		<?=(new UI\StylisedInput("lat", "lat"))->setType("number")->setIsRequired(false);?>
		<?=(new UI\StylisedInput("lng", "lng"))->setType("number")->setIsRequired(false);?>
		<?=(new UI\StylisedInput("radius", "Радиус"))->setIsRequired(false);?>
		<?=(new UI\StylisedInput("description", "Описание"))->setType("textarea")->setIsRequired(false);?>

		<input type="submit" value="Готово" />
	</form>
</div>

<table class="admin-banTable">
	<thead>
	<tr>
		<td>ID</td>
		<td>Название</td>
		<td>Дочернее место</td>
		<td>lat</td>
		<td>lng</td>
		<td>Радиус</td>
		<td>Описание</td>
		<td>Действия</td>
	</tr>
	</thead>
<?
	foreach ($items as $city) {
		$parent = $city->getParentId() ? $assoc[$city->getParentId()] : null;
?>
	<tr data-city-id="<?=$city->getId();?>" id="city<?=$city->getId();?>">
		<td><?=$city->getId();?></td>
		<td class="admin-city-editable" data-key="name"><?=$city->getName();?></td>
		<td data-key="parentId" data-city-name="<?=$parent ? $parent->getName() : "&mdash;";?>"><?=$city->getParentId();?></td>
		<td class="admin-city-editable" data-key="lat"><?=$city->getLat();?></td>
		<td class="admin-city-editable" data-key="lng"><?=$city->getLng();?></td>
		<td class="admin-city-editable" data-key="radius"><?=$city->getRadius();?></td>
		<td class="admin-city-editable" data-key="description"><?=$city->getDescription();?></td>
		<td><button type="button" onclick="Admin.removeCity(this);">Удалить</button></td>
	</tr>
<?
	}
?>
</table>
