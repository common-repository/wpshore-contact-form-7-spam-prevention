<?php
/*
Plugin Name: WPshore Contact Form 7 - Spam Prevention
Plugin URI: http://www.wpshore.com/plugins/contact-form-7-spam-prevention/
Description: A plugin for Contact Form 7 - Spam Prevention.
Version: 1.0
Author: Nablasol 
Author URI: http://www.nablasol.com
*/
add_action('admin_menu', 'cf7sp_third_party_submenu');//creating submenu to the contact
function cf7sp_third_party_submenu() 
{
	add_submenu_page(
	'wpcf7', // Third party plugin Slug 
	'Limit Spam', 
	'Limit Spam', 
	'delete_plugins', 
	'cf7sp_third_party_submenu', 
	'cf7sp_limit_spam_page'
	);
}

function cf7sp_limit_spam_page() //admin page for Limit Spam
{
	?>
	<div style="margin-bottom: 20px;position: relative;border: 1px solid #c7c7c7;padding: 6px;margin-top: 44px;width: 1168px;height: 540px;">
	<h1 style="font: bold 20px serif;">Limit Sapm</h1>
	<p style="color: #333;margin: 2ex 0 1ex 0;"></p><?php echo esc_html( __( "Copy this code and paste it into the contact form 7 left and you can change the allowedurl value as per your wish." )); ?></p>
	<input style="width:290px;padding-left:20px;" type="text" value ="[preventspamurl allowedurl value:2]"onfocus="this.select();" readonly="readonly" /><br/><br/><br/>
	<h1 style="font: bold 20px serif;">ScreenShot</h1>
	<?php echo '<img src="' . plugins_url('cf7_spam_prevention/limit.png' , dirname(__FILE__) ) . '" > ';?>   
	</div>			
	<?php 
}

add_action('plugins_loaded', 'cf7sp_limit_spam_loader', 10); //loading plugin if cf7 is installed
function cf7sp_limit_spam_loader() 
{
	global $pagenow;
	
	if (function_exists('wpcf7_add_shortcode')) {
		wpcf7_add_shortcode('preventspamurl', 'cf7sp_preventspamurl_shortcode_handler', true);
	} else {
		
		if ($pagenow != 'plugins.php') { return; }
		add_action('admin_notices', 'cf7sp_error_display');
		wp_enqueue_script('thickbox');
	}//end of if condition
}

function cf7sp_error_display() 
{
	$out = '<div class="error" id="messages"><p>';
	if(file_exists(WP_PLUGIN_DIR.'/contact-form-7/wp-contact-form-7.php')) 
	{
		$out .= __('The Contact Form 7 is installed, but <strong>you must activate Contact Form 7</strong> below for the Limit Sapm Module to work.');
	} else {
		$out .= __('The Contact Form 7 plugin must be installed for the Limit Spam Module to work. <a href="'.admin_url('plugin-install.php?tab=plugin-information&plugin=contact-form-7&from=plugins&TB_iframe=true&width=600&height=550').'" class="thickbox" title="Contact Form 7">Install Now.</a>');
	}
	$out .= '</p></div>';	
	echo $out;
}

function cf7sp_preventspamurl_shortcode_handler($tag) //shortcode code 
{
	if ( ! is_array( $tag ) )
	return '';
	$options = (array) $tag['options'];
	foreach ( $options as $option ) 
	{
		if ( preg_match( '%^value:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) 
		{
			$value_att .= $matches[1];
		}
	}
	
	$html = '<input type="hidden" name="allowedurl" value="'.$value_att.'" />';
	return $html;
}

add_filter( 'wpcf7_spam', 'cf7sp_validate_text' );
//spam validation
function  cf7sp_validate_text($spam) 
{			
	
	$post = $_POST;
	$spam_post_url = 0;
	if ( isset($_POST['allowedurl']) && is_numeric($_POST['allowedurl']) )
	{		$preventspamurl = $_POST['allowedurl'];
	}else
	{
		$preventspamurl = 1;
	}
	foreach($post as $key => $value) 
	{
		if(stristr($value, 'http://'))
		{
			$spam_post_url += substr_count($value, 'http://');		
		}
		if(stristr($value, 'https://'))
		{
			$spam_post_url += substr_count($value, 'https://');
		}
		if(stristr($value, '[url='))
		{
			$spam_post_url += substr_count($value, '[url=');
		}
		
	} //end of loop
	if( $spam_post_url > $preventspamurl ) 
	{
		return true;
	}
	return false;
}