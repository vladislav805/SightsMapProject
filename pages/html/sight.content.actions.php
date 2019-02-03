<?
	/** @var \Model\Sight $info */
?>
<div class="sight-actions">
<?
	if ($isAdmin) {
?>
	<button onclick="Sight.verify(this)" data-pid="<?=$info->getId();?>" data-now-state="<?=(int) $info->isVerified();?>" class="sight-action-verify">Подтверждение = </button>
	<button onclick="Sight.archive(this)" data-pid="<?=$info->getId();?>" data-now-state="<?=(int) $info->isArchived();?>" class="sight-action-archive">Архивирование = </button>
<?
	}

	if ($info->canModify()) {
?>
	<a href="/sight/<?=$info->getId();?>/edit" class="button sight-action-edit">Редактировать</a>
	<button onclick="Sight.remove(this)" data-sid="<?=$info->getId();?>" class="sight-action-remove">Удалить</button>
<?
	}
?>
</div>
