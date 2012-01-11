<?php
/*
Plugin Name: Foursquare Button
Plugin URI: http://carling.otherlocker.info/
Description: Wordpress... Y U NO GO TO HELL?
Version: 0.1.1.1.1.1.1.1.1.11.1.1.1.1.1.1.1.1.1.11.1..1.11.1.1.1.11.1000.1.1.2
Author: Mario Alvarez
Author URI: http://dsafasd.com
License: WTFPL :p
*/

/**
 * Init
 */
add_action('admin_init', '_4sq_admin_init');
add_action('admin_menu', '_4sq_admin_menu');


/**
 * Register global options
 */
function _4sq_admin_init ()
{
	/**
	 * Metabox for post-based configuration venues
	 */
	add_meta_box('_4sq_metabox', 'Foursquare Button', '_4sq_metabox', 'post', 'side', 'default');


	/**
	 * Settings
	 */
	register_setting('_4sq_options', '_4sq_id_user', 'intval');
	register_setting('_4sq_options', '_4sq_buttonsize');


	/**
	 * Hook Foursquare to save action, muaaaahahahahaha
	 */
	add_action('save_post', '_4sq_savepost');
}


/**
 * Add configs page
 */
function _4sq_admin_menu ()
{
	add_options_page('Foursquare Button global options', 'Foursquare Button', 'manage_options', '_4sq-button-options', '_4sq_options');
}


/**
 * Options
 */
function _4sq_options ()
{
	/**
	 * Pull options
	 */
	$options = array(
		'_4sq_buttonsize' => get_option('_4sq_buttonsize')
	);

	?>
	<div class="wrap">
		<h2>Foursquare Button global options</h2>
		<form id="foursq_options" method="POST" action="options.php">
			<?php settings_fields('_4sq_options'); ?>
			<fieldset>

				<p class="meta_options">
					<label for="_4sq_id_user">Foursquare User no.</label>
					<input type="text" name="_4sq_id_user" value="<?php echo get_option('_4sq_id_user'); ?>" />
				</p>

				<p class="meta_options">
					<label>Choose a button size:</label><br />
					<div>
						<input class="radio" type="radio" name="_4sq_buttonsize" value="wide" <?php echo $options['_4sq_buttonsize'] == 'wide' ? 'checked="checked"' : NULL; ?> />
						<a class="buttonPreview saveWide"></a>
					</div>
					<div>
						<input class="radio" type="radio" name="_4sq_buttonsize" value="standard" <?php echo $options['_4sq_buttonsize'] == 'standard' ? 'checked="checked"' : NULL; ?> />
						<a class="buttonPreview saveStandard"><a/>
					</div>
				</p>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>

			</fieldset>
		</form>
	</div>
	<style type="text/css">

	#foursq_options .buttonPreview {
		height: 20px;
		overflow: hidden;
		display: inline-block;
	}

		#foursq_options .buttonPreview.saveWide {
			background-image: url(https://static-s.foursquare.com/img/intent/save-w-e6176257636ee7760d3cb728373605ec.png);
			width: 126px;
		}
	
		#foursq_options .buttonPreview.saveStandard {
			background-image: url(https://static-s.foursquare.com/img/intent/save-9d359e32709c5ea2cad35220c778d641.png);
			width: 55px;
		}

		#foursq_options input.radio {
			margin: 3px 3px 0px 5px;
			float: left;
		}

	</style>
	<?php
}


/**
 * Metabox
 */
function _4sq_metabox ($post, $box)
{

	/**
	 * Pull post_meta
	 */
	$meta = array(
		'status' => get_post_meta($post->ID, '_4sq_post-enabled', TRUE),
		'venue' => get_post_meta($post->ID, '_4sq_post-venue', TRUE),
	);


	?>
	<p class="meta_options">
		<label for="4sq_post-enabled"></label>
		<select name="4sq_post-enabled" id="4sq_post-enabled">
			<option value="0" <?php echo (is_null($meta['status']) || ! $meta['status'] ? 'selected="selected"' : NULL) ?>>Disabled</option>
			<option value="1" <?php echo ($meta['status'] ? 'selected="selected"' : NULL) ?>>Enabled</option>
		</select>
	</p>

	<p class="meta_options">
		<label for="4sq_post-venue">Venue name:</label><br />
		<input type="text" name="4sq_post-venue" id="4sq_post-venue" value="<?php echo $meta['venue']; ?>" />
	</p>

	<?php
}


/**
 * Save Foursquare data when post is saved
 */
function _4sq_savepost ($post_id)
{

	if ( ! $post_id)
	{
		/**
		 * Get post ID
		 */
		global $wp_query;
		$post_id = $wp_query->post->ID;
	}


	if (isset($_POST['4sq_post-enabled']))
		update_post_meta($post_id, '_4sq_post-enabled', $_POST['4sq_post-enabled']);

	if (isset($_POST['4sq_post-venue']))
		update_post_meta($post_id, '_4sq_post-venue', $_POST['4sq_post-venue']);
}




/**
 * 
 */
if ( ! function_exists('_4sq_button'))
{
	function _4sq_button ()
	{

		/**
		 * Get post ID
		 */
		global $wp_query;
		$post_id = $wp_query->post->ID;


		/**
		 * Check if enabled
		 */
		if ( ! $enabled = (bool) get_post_meta($post_id, '_4sq_post-enabled', TRUE))
			return;


		/**
		 * This plugin its supposed to work only on single posts
		 */
		if ( ! is_single())
			return;
	

		/**
		 * Pull post_meta
		 */
		$meta = array(
			'status' => get_post_meta($post->ID, '_4sq_post-enabled', TRUE),
			'venue' => get_post_meta($post->ID, '_4sq_post-venue', TRUE),
			'buttontype' => get_option('_4sq_buttonsize')
		);

		ob_start();
		if ((bool) @ini_get('short_open_tag') === FALSE AND config_item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents('vCard.php'))));
		}
		else
		{
			include('vCard.php'); // include() vs include_once() allows for multiple views with the same name
		}
		$buffer = ob_get_contents();
		@ob_end_clean();
		echo $buffer;
	}
}


/**
 * Public script
 */
if ( ! function_exists('_4sq_script'))
{
	function _4sq_script ()
	{
		return "
		<script type='text/javascript'>
		  (function() {
		    window.___fourSq = {\"uid\":\"" . get_option('_4sq_id_user') . "\"};
		    var s = document.createElement('script');
		    s.type = 'text/javascript';
		    s.src = 'http://platform.foursquare.com/js/widgets.js';
		    s.async = true;
		    var ph = document.getElementsByTagName('script')[0];
		    ph.parentNode.insertBefore(s, ph);
		  })();
		</script>";
	}
}