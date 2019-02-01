<?
	/** @var array $counts */
?>
<div class="index-counts">
	<div class="index-count">
		<div class="index-count-label">всего <span class="index-count-number"><?=$counts["total"];?></span> <?=pluralize($counts["total"], ["место", "места", "мест"]);?></div>
		<div class="index-count-label">... из них:</div>
	</div>
	<div class="index-count-verbose">
		<div class="index-count">
			<div class="index-count-number"><?=$counts["verified"];?></div>
			<div class="index-count-label">подтверждено</div>
		</div>
		<div class="index-count">
			<div class="index-count-number"><?=$counts["archived"];?></div>
			<div class="index-count-label">уже не существует</div>
		</div>
	</div>
</div>