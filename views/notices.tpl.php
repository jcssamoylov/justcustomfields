<?php global $wp_version;?>
<div  class="updated notice <?php echo (($msg[0] == 'error') ? $msg[0] . ' is-dismissible' : 'is-dismissible') ?> below-h2 ">
	<p><?php echo $msg[1]; ?></p>
	<?php echo ($wp_version < 4.2 ? '' : '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __('Dismiss this notice.', JCF_TEXTDOMAIN) . '</span></button>') ?>
</div>