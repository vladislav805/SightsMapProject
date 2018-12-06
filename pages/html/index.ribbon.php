<?
	/** @var array $counts */
?>
<div class="index-counts">
	<div class="index-count">
		<h5>всего</h5>
		<h4><?=$counts["total"];?></h4>
		<h5><?=pluralize($counts["total"], "место", "места", "мест");?></h5>
	</div>
	<div class="index-count">
		<h5>... из них:</h5>
		<h4><?=$counts["verified"];?></h4>
		<h5>подтверждено</h5>
		<h4><?=$counts["archived"];?></h4>
		<h5>уже не существует</h5>
	</div>
</div>