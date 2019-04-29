<?
	/** @var \Model\ListCount $data */

	list($data) = $data;

	if (!($data instanceof \Model\ListCount)) {
		exit;
	}

	$items = $data->getItems();

	/** @var \Model\Moderator[] $items */
	$items = $data->getItems();

?>
<div class="spoiler">
	<div class="spoiler-head">Изменить статус пользователя</div>
	<form class="spoiler-content" action="#" method="post" id="__adminJobSet">
		<?=(new UI\StylisedInput("userId", "UserID"));?>
		<?=(new UI\StylisedSelect("status", "Должность", [
			["value" => "USER", "label" => "пользователь"],
			["value" => "MODERATOR", "label" => "модератор"],
			["value" => "ADMIN", "label" => "администратор"]
		]));?>
		<input type="submit" value="Готово" />
	</form>
</div>

<table class="admin-jobTable">
<?
	foreach ($items as $user) {
?>
	<tr class="admin-job-user-<?=$user->getId();?>">
		<td><?=$user->getId();?></td>
		<td><?=htmlSpecialChars($user->getFirstName() . " " . $user->getLastName());?></td>
		<td><?=$user->getStatus();?></td>
		<td><a href="/user/<?=$user->getLogin();?>" class="button">Перейти</a> <button class="a" onclick="Admin.focusSetJob(this);" data-user-id="<?=$user->getId();?>" data-user-status="<?=$user->getStatus();?>">Редактировать</button></td>
	</tr>
<?
	}
?>
</table>
