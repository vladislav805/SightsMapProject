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

	$isOwner = $meId === $info->getOwnerId();

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
		<div class="comment-text"><?=highlightURLs(htmlspecialchars($c->getText()));?></div>
		<div class="comment-footer">
<?
			print getRelativeDate($c->getDate());
			if ($c->getUserId() === $meId || $isOwner) {
?> | <span class="comment-action" onclick="Comment.remove(this);" data-cid="<?=$c->getId();?>">Удалить</span>
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
</div>