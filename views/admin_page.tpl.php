<?php include(JCF_ROOT . '/views/_head_wrapper.tpl.php'); ?>

	<div class="jcf_tab-content">
		<div class="jcf_inner-tab-content" >
			<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
			<p><?php _e('You should choose Custom Post Type first to configure fields for it:', JCF_TEXTDOMAIN); ?></p>
			<div>
				<ul class="dotted-list jcf-bold">
				<?php foreach($post_types as $key => $obj) : ?>
					<?php $fieldsets_count = $this->model->countFields($key); ?>
					<li>
						<a class="jcf_tile jcf_tile_<?php echo $key; ?>" href="?page=jcf_fields&amp;pt=<?php echo $key; ?>">
							<span class="jcf_tile_icon"></span>
							<span class="jcf_tile_title"><?php echo $obj->label; ?>
								<span class="jcf_tile_info">
									<?php _e('Added Fieldsets: ', JCF_TEXTDOMAIN); ?><?php echo $fieldsets_count['fieldsets']; ?>
									<?php _e('Total Fields:  ', JCF_TEXTDOMAIN); ?><?php echo $fieldsets_count['fields']; ?>
								</span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>

<?php include(JCF_ROOT . '/views/_foot_wrapper.tpl.php'); ?>