<?php include(JCF_ROOT . '/views/_head_wrapper.tpl.php'); ?>

<div class="jcf_tab-content">
	<div class="jcf_inner-tab-content" >
		<form action="<?php get_permalink(); ?>" id="jcform_settings" method="post" class="jcf_form_horiz" onsubmit="return initSettings();">

			<?php if( MULTISITE ): ?>
				<div class="card pressthis">
					<h3 class="header"><?php _e('MultiSite settings:', \jcf\JustCustomFields::JCF_TEXTDOMAIN); ?></h3>
					<fieldset>
						<input type="radio" name="jcf_multisite_setting" id="jcf_setting_global" 
							   value="<?php echo \jcf\models\Settings::JCF_CONF_MS_NETWORK; ?>" <?php checked($network, \jcf\models\Settings::JCF_CONF_MS_NETWORK); ?> />
						<label for="jcf_setting_global"><?php _e('Make fields settings global for all network', \jcf\JustCustomFields::JCF_TEXTDOMAIN); ?> </label><br />

						<input type="radio" name="jcf_multisite_setting" id="jcf_setting_each" 
							   value="<?php echo \jcf\models\Settings::JCF_CONF_MS_SITE; ?>" <?php checked($network, \jcf\models\Settings::JCF_CONF_MS_SITE); ?> />
						<label for="jcf_setting_each"><?php _e('Fields settings are unique for each site', \jcf\JustCustomFields::JCF_TEXTDOMAIN); ?> </label><br /><br />
					</fieldset>
				</div>
			<?php endif; ?>

			<div class="card pressthis">
				<h3 class="header"><?php _e('Settings storage configuration:', \jcf\JustCustomFields::JCF_TEXTDOMAIN); ?></h3>

				<input type="radio" class="jcf_choose_settings" name="jcf_read_settings" 
					   value="<?php echo \jcf\models\Settings::JCF_CONF_SOURCE_DB; ?>" id="jcf_read_db" <?php  checked($source, \jcf\models\Settings::JCF_CONF_SOURCE_DB); ?>/>
				<label for="jcf_read_db"><?php _e('<b>Database</b>. You can\'t edit or move settings without export/import features (default)' , \jcf\JustCustomFields::JCF_TEXTDOMAIN); ?></label><br />

				<input type="radio" rel="" class="jcf_choose_settings" name="jcf_read_settings" 
					   value="<?php echo \jcf\models\Settings::JCF_CONF_SOURCE_FS_THEME; ?>" id="jcf_read_file"  <?php checked($source, \jcf\models\Settings::JCF_CONF_SOURCE_FS_THEME); ?>/>
				<label for="jcf_read_file"><?php _e('<b>File system: Current theme folder</b>. Fields configuration is saved to the current theme folder in json format and can be copied to another site easily.' , \jcf\JustCustomFields::JCF_TEXTDOMAIN); ?></label><br />

				<?php $show_fs_global = MULTISITE && $network == \jcf\models\Settings::JCF_CONF_MS_NETWORK; ?>
				<input type="radio" rel="" <?php if(!$show_fs_global) echo 'style="display:none;"'; ?> 
					   class="jcf_choose_settings" name="jcf_read_settings" 
					   value="<?php echo \jcf\models\Settings::JCF_CONF_SOURCE_FS_GLOBAL; ?>" id="jcf_read_file_global"  <?php checked($source, \jcf\models\Settings::JCF_CONF_SOURCE_FS_GLOBAL); ?>/>
				<label for="jcf_read_file_global" <?php if(!$show_fs_global) echo 'style="display:none;"'; ?>><?php _e('<b>File system: Global</b> (/wp-content/jcf-settings/*). Fields configuration is saved to the wp-content folder in json format and can be copied to another site easily.' , \jcf\JustCustomFields::JCF_TEXTDOMAIN); ?></label><br />

				<input type="hidden" name="jcf_keep_settings" value="1" disabled="disabled" />
			</div>
			<br /><br />
			<?php wp_nonce_field("just-nonce"); ?>
			<input type="submit" class="button-primary jcf_update_settings" name="jcf_update_settings" value="<?php _e('Save all settings', \jcf\JustCustomFields::JCF_TEXTDOMAIN); ?>" />
		</form>
	</div>
</div>

<?php include(JCF_ROOT . '/views/_foot_wrapper.tpl.php'); ?>
