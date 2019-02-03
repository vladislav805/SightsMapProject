<?
	/** @var \Model\ListCount $comments */
	/** @var \Model\Sight $info */

	use Model\Comment;
	use Model\User;

?>
<div class="sight-comments-wrap">
	<h4>Комментарии</h4>
	<div class="comments-items" data-sid="<?=$info->getId();?>">
<?
	/** @var User|null $currUser */
	$currUser = $this->mController->getUser();
	$meId = $currUser ? $currUser->getId() : -1;

	$isAdmin = $currUser !== null && ($currUser->getStatus() === User::STATE_MODERATOR || $currUser->getStatus() === User::STATE_ADMIN);
	$isOwner = $meId === $info->getOwnerId() || $isAdmin;

	if ($comments->getCount()) {
		$users = [];

		/** @var User[] $u */
		$u = $comments->getCustomData("users");
		foreach ($u as $item) {
			$users[$item->getId()] = $item;
		}
		$u = null;

		/** @var Comment[] $cItems */
		$cItems = $comments->getItems();
		foreach ($cItems as $c) {
			/** @var User $u */
			$u = $users[$c->getUserId()];
?>
<div class="comment-item" data-comment-id="<?=$c->getId();?>">
	<div class="comment-author-photo" style="background-image: url('<?=$u->getPhoto()->getUrlThumbnail();?>')"></div>
	<div class="comment-content">
		<h6 class="comment-author-name"><a href="/user/<?=$u->getLogin();?>"><?=htmlspecialchars($u->getFirstName() . " " . $u->getLastName());?></a></h6>
		<div class="comment-text"><?=formatText($c->getText());?></div>
		<div class="comment-footer">
<?
			print getRelativeDate($c->getDate());
			if ($c->getUserId() === $meId || $isOwner) {
?> | <span class="a comment-action" onclick="Comments.removeComment(this);" data-cid="<?=$c->getId();?>">Удалить</span>
<?
			}
?>
		</div>
	</div>
</div>
<?
		}
	} else {
		printf("Нет комментариев");
	}

?>
</div>
<?
	if ($currUser) {
?>
<form action="#" method="post" data-sid="<?=$info->getId();?>" class="comment-form">
	<?=(new \UI\StylisedInput("text", "Новый комментарий"))->setType("textarea");?>
	<input type="submit" value="send">
</form>
<?
	}
?>
</div>