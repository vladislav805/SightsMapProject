<?
	/** @var \Model\ListCount $data */

	list($data) = $data;

	if (!($data instanceof \Model\ListCount)) {
		exit;
	}

	/** @var \Model\BannedUser[] $items */
	$items = $data->getItems();

?>
<div class="spoiler">
	<div class="spoiler-head">Заблокировать юзера</div>
	<form class="spoiler-content" action="#" method="post" id="__adminBanAdd">
		<?=(new UI\StylisedInput("userId", "UserID"));?>
		<?=(new UI\StylisedSelect("reasonId", "Причина", [
				["value" => 1, "label" => "другая причина"],
				["value" => 2, "label" => "дезинформация"],
				["value" => 3, "label" => "враждебное поведение"],
				["value" => 4, "label" => "спам"],
				["value" => 5, "label" => "нериемлимый контент"],
				["value" => 6, "label" => "-"]
		]));?>
		<?=(new UI\StylisedInput("comment", "Комментарий"))->setType("textarea")->setIsRequired(false);?>
		<input type="submit" value="Готово" />
	</form>
</div>

<table class="admin-banTable">
<?
	foreach ($items as $user) {
?>
	<tr class="admin-banned-user-<?=$user->getId();?>">
		<td><?=$user->getBanId();?></td>
		<td><?=$user->getId();?></td>
		<td><img class="admin-banTable-photo" src="<?=$user->getPhoto()->getUrlThumbnail();?>" alt="photo" /></td>
		<td><?=$user->getFirstName() . " " . $user->getLastName();?></td>
		<td><?=$user->getReason();?></td>
		<td><?=$user->getComment();?></td>
		<td><a href="/user/<?=$user->getLogin();?>" class="button">Перейти</a><button type="button" onclick="Admin.unbanUser(this);" data-uid="<?=$user->getId();?>">Разблокировать</button></td>
	</tr>
<?
	}
?>
</table>
