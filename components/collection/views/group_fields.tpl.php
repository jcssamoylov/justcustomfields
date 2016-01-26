<div class="collection_field_group">
	<h3>
		<span class="dashicons dashicons-editor-justify"></span>
		<span class="collection_group_title">
		<?php echo $collection->instance['title'].' Item'; ?>
		</span>
		<span class="dashicons dashicons-trash"></span>

	</h3>
	<div class="collection_field_group_entry">
		<?php foreach ( $collection->instance['fields'] as $field_id => $field ) : ?>
				<div class="collection_field_border jcf_collection_<?php echo ( intval($field['field_width']) ? $field['field_width'] : '100' ); ?>">
				<?php echo $field['field']; ?>
				</div>
		<?php endforeach; ?>
		<div class="clr"></div>
	</div>
</div>
