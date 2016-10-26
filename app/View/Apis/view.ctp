<?php
	$this->Html->css('secondary', null, array('inline' => false));
	$this->Html->css('search', null, array('inline' => false));
	$hasSelectable = false;
?>
<style type="text/css">
	table.api-terms tr:hover {
		background-color: #eee
	}
	table.api-terms tr.header:hover {
		background-color: inherit;
	}
</style>
<div id="searchBody" class="innerLower">
	<div id="searchResults">
		<h1 class="headerTab"><?= $hostname . '/' . trim($basePath, '/') ?></h1>
		<div class="clear"></div>
		<div id="srLower" class="whiteBox">
			<div class="resultItem">
				<?php if ($isAdmin): ?>
					<div style="float: right">
						<?= $this->Html->link(
							'Update Unlinked Terms',
							array_merge(['controller' => 'api_admin', 'action' => 'update', $hostname], explode('/', $basePath)),
							['class' => 'inputButton']) ?>
					</div>
				<?php endif ?>
				<table class="api-terms checkBoxes">
					<tr class="header">
						<th>Field</th>
						<th>Business Term</th>
					</tr>
					<?php foreach ($terms as $term): ?>
						<tr>
							<td><?= $term->name ?></td>
							<td>
								<?php if (!empty($term->businessTerm[0])): ?>
									<?php $hasSelectable = true; ?>
									<?= $this->Html->link($term->businessTerm[0]->term, ['controller' => 'search', 'action' => 'term', $term->businessTerm[0]->termId]) ?>
								<?php endif ?>
							</td>
							<td>
								<?php if (!empty($term->businessTerm[0])): ?>
									<input
										type="checkbox"
										name="terms[]"
										data-title="<?= h($term->businessTerm[0]->term) ?>"
										data-vocabID="<?= h($term->businessTerm[0]->termCommunityId) ?>"
										value="<?= h($term->businessTerm[0]->termId) ?>"
										checked="checked">
								<?php endif ?>
							</td>
						</tr>
					<?php endforeach ?>
				</table>
				<?php if ($hasSelectable): ?>
					<input type="button" onclick="addToQueue(this, false)" class="requestAccess grow mainRequestBtn" value="Add To Request">
				<?php endif ?>
			</div>
		</div>
	</div>
</div>