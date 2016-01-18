<?php global $wp_version;?>
<?php foreach ( $all_messages as $message ):?>
	<div  class="updated notice <?php echo (($message[0] == 'error') ? $message[0] . ' is-dismissible' : 'is-dismissible') ?> below-h2 ">
		<p><?php echo $message[1]; ?></p>
		<?php echo ($wp_version < 4.2 ? '' : '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __('Dismiss this notice.', \jcf\JustCustomFields::TEXTDOMAIN) . '</span></button>') ?>
	</div>
<?php endforeach; ?>