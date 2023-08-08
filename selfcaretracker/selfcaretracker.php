<?php
/**
 * Plugin Name: Self Care Tracker
 * Plugin URI: http://selfcaretracker.com
 * Description: A behavior Tracker for self-care
 * Version: 1.3.2
 * Author: Purify Your Gaze Training - Terran Orletsky
 * Author URI: http://purifyyourgazetraining.com
 * License: Private
 */
/**/
// set global path and url vars for plugin


if((!is_admin() && stristr($_SERVER["REQUEST_URI"],'/tracker')) || (!is_admin() && stristr($_SERVER["REQUEST_URI"],'api')) || is_admin()){ 
 
global $sct_plugin_path, $sct_plugin_url;
$sct_plugin_path = plugin_dir_path( __FILE__ );
$sct_plugin_url = plugin_dir_url( __FILE__ );
// load custom post types used by plugin
// note: uses wp-types embedded version
// see:  http://wp-types.com/documentation/embedded-types-and-views/embedding-types/
require_once $sct_plugin_path . 'library/embedded-types/types.php';
// include plugin library functions
include($sct_plugin_path . 'library/functions/functions-custom.php');
include($sct_plugin_path . 'library/functions/functions-posttypes.php');
include($sct_plugin_path . 'library/functions/functions-widgets.php');


include($sct_plugin_path . 'library/functions/functions-json.php');
include($sct_plugin_path . 'library/functions/functions-ajax.php');


    // enqueue plugin scripts and styles
  add_action( 'wp_enqueue_scripts', 'sct_enqueue_scripts', 10 );
}


function sct_enqueue_scripts() {
	global $sct_plugin_path, $sct_plugin_url;
	if(!is_admin()): //NOT  admin panel
		// enqueue plugin CSS styles
		// wp_enqueue_style( $handle, $src, $deps, $ver, $media ); 
		// http://codex.wordpress.org/Function_Reference/wp_enqueue_style		
		wp_enqueue_style( 'sct-tracker', $sct_plugin_url.'templates/default/css/style-tracker.css', array(), '1.0', 'all' );
		wp_enqueue_style( 'sct-calendar', $sct_plugin_url.'templates/default/css/style-calendar.css', array('sct-tracker'), '1.0', 'all' );
		wp_enqueue_style( 'sct-print', $sct_plugin_url.'templates/default/css/style-print.css', array('sct-tracker'), '1.0', 'print' );
		wp_enqueue_style( 'sct-normalize', $sct_plugin_url.'templates/default/css/style-normalize.css', array('sct-tracker'), '1.0', 'all' );
		wp_enqueue_style( 'sct-datepicker', $sct_plugin_url.'templates/default/css/style-datepicker.css', array('sct-tracker'), '1.0', 'all' );
		//enqueue google fonts
		wp_enqueue_style( 'googlefont-courgette', 'http'. ($_SERVER['SERVER_PORT'] == 443 ? 's' : '') .'://fonts.googleapis.com/css?family=Courgette&subset=latin,latin-ext', array(), false, 'all' );
		wp_enqueue_style( 'googlefont-rancho', 'http'. ($_SERVER['SERVER_PORT'] == 443 ? 's' : '') .'://fonts.googleapis.com/css?family=Rancho', array(), false, 'all' );
		wp_enqueue_style( 'googlefont-raleway', 'http'. ($_SERVER['SERVER_PORT'] == 443 ? 's' : '') .'://fonts.googleapis.com/css?family=Raleway:400,700', array(), false, 'all' );
		wp_enqueue_style( 'google-fonts', 'http'. ($_SERVER['SERVER_PORT'] == 443 ? 's' : '') .'://fonts.googleapis.com/css?family=Lato', array(), false, 'all' );
		// enqueue css conditionals for IE
		if(preg_match('/(?i)msie [1-8]/',$_SERVER['HTTP_USER_AGENT']))
		{
			// if IE<=8
			wp_enqueue_style( 'sct-IElegacy', $sct_plugin_url.'templates/default/css/style-tracker-ie8.css', array('sct-tracker','sct-calendar'), '1.0', 'all' );
		}
		elseif(preg_match('/(?i)msie 9/',$_SERVER['HTTP_USER_AGENT']))
		{
			// if IE=9
			wp_enqueue_style( 'sct-IE9', $sct_plugin_url.'templates/default/css/style-tracker-ie9.css', array('sct-tracker','sct-calendar'), '1.0', 'all' );
		}
		// enqueue plugin JS scripts
		// wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
		// http://codex.wordpress.org/Function_Reference/wp_enqueue_script
		//custom js tools - load first
		wp_enqueue_script( 'betterJS', $sct_plugin_url.'js/betterJS.js', array(), 1.1, false );
		//the basics
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-effects-highlight' );
		// jquery plugins
		wp_enqueue_script( 'jquery-scrollto', $sct_plugin_url.'js/jquery.scrollTo.min.js', array('betterJS','jquery'), '1.4.3.1', false );
		wp_enqueue_script( 'jquery-elastic', $sct_plugin_url.'js/jquery.elastic.js', array('betterJS','jquery'), '1.6.11', false );
		//knockoutjs + plugins
		wp_enqueue_script( 'knockout', $sct_plugin_url.'js/knockout.js', array('betterJS','jquery'), '2.1.0', false );
		wp_enqueue_script( 'knockout-sortable', $sct_plugin_url.'js/knockout-sortable.min.js', array( 'knockout'), '1.0', false );
		wp_enqueue_script( 'knockout-editable', $sct_plugin_url.'js/knockout-editable.js', array('knockout'), '0.9', false );
	else: // IS admin panel
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	endif; // IS/ IS NOT admin panel
}


  //initialize the plugin on load
  add_action('init', 'selfcaretracker_init');



function selfcaretracker_init() {
	//reset vars
	$do_not_init = $sbc_missing = $json_missing = false;
	//check for dependent plugins
	// json-spi plugin
	if (!SCT_is_plugin_active('json-api/json-api.php')) {
		$json_api_missing = true;
		$do_not_init = true;
	}
	if(!$do_not_init) {
		// all is good - req'd plugins are in place - proceed
		//check that custom controllers are active for json api
		global $json_api;
		$sct_controllers = array('core','scrcategory','scrcustom','customposts','terms','progress','options','trackersubmit','progressajax');
		$active_controllers = explode(',', get_option('json_api_controllers', 'core'));
		$available_controllers = $json_api->get_controllers();
		foreach ($available_controllers as $controller) {
			if (in_array($controller, $sct_controllers)&&!in_array($controller, $active_controllers)) {
				$active_controllers[] = $controller;
			}
		}
		//set required controllers to be actove
		$json_api->save_option('json_api_controllers', implode(',', $active_controllers));
	} else {
		//only show notices on admin section for admins
		if(is_admin()&&current_user_can('edit_users')){
			if($json_api_missing)
				add_action('sct_admin_notices', 'selfcaretracker_plugin_jsonapi_warning');
			add_action('admin_notices', 'selfcaretracker_plugin_warning');
		} elseif(!is_admin()&& !in_array( $GLOBALS['pagenow'], array( 'wp-login.php'))) {
			// show error on front end and exit
			die('The SelfCare Tracker plugin requires additional plugins to be installed - Please notify a site administrator of this error.');
			exit;
		}
	}
	return;
}
// used to check for plugin dependencies
function SCT_is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
	// is_plugin_active_for_network( $plugin )
}
// admin nofications if dependencies are not loaded  
function selfcaretracker_plugin_warning() {
	echo "<div id=\"selfcaretracker-warning\" class=\"error\"><h2>Self Care Tracker - Installation Error</h2><p>The following plugin is required for the Selfcare Tracker plugin to work correctly. Please use the links below to install the free plugin before proceeding:</p><ul>";
	do_action('sct_admin_notices');
	echo "</ul></div>";
}
// plugin dependency warning
function selfcaretracker_plugin_jsonapi_warning() {
	echo "<li id=\"selfcaretracker-jsonapi-warning\"><strong>JSON API</strong> : <span id=\"selfcaretracker-jsonapi-warning-content\">";
	if ( is_multisite() ) { //Multisite is enabled
		$ms_prepend = "network/";
	} else {
		$ms_prepend = "";
	}
	if(!file_exists('../json-api/json-api.php')){
			if(in_array( $GLOBALS['pagenow'], array( 'plugins.php'))){
			echo '<script type="text/javascript">jQuery( document ).ready(function($) {jQuery("#json-api span.activate").clone().prependTo("#selfcaretracker-jsonapi-warning-content");});</script>';
			$json_nonce = wp_create_nonce('activate');
			echo " <a href=\"./{$ms_prepend}plugin-install.php?tab=plugin-information&plugin=json-api&TB_iframe=true&width=640&height=619\" class=\"thickbox\">Details</a>
						</li>";
			} else {
			echo "<a href=\"./plugins.php\">Go to Plugins page</a>";
			}
	} else {
		echo "<a href=\"./{$ms_prepend}plugin-install.php?tab=plugin-information&plugin=json-api&TB_iframe=true&width=640&height=619\" class=\"thickbox\">Details / Install</a></li>";
	}
}
// load custom view/controllers into the json-api plugin, for use with this plugin
function sct_get_jsonapi_controllers() {
	$controllers = array();
	$dir = json_api_dir();
	$this->check_directory_for_controllers("$dir/controllers", $controllers);
	$this->check_directory_for_controllers(get_stylesheet_directory(), $controllers);
	$controllers = apply_filters('json_api_controllers', $controllers);
	return array_map('strtolower', $controllers);
}
// Add initialization and activation hooks
function selfcaretracker_activation() {
	$page_array= array(
			array('tracker','Your Self-Care Tracker','Admin page - Do not Delete'),
			array('tracker-stats','Tracker Stats','Admin page - Do not Delete'),
			array('tracker-settings','Tracker Settings','Admin page - Do not Delete'),
			array('tracker-help','Tracker Help / FAQ','FAQ\'s go here'),
			array('tracker-intro','Self-Care Tracker Intro','<p style="text-align: center;"><img class="size-full wp-image-2759 alignnone" title="Self-Care-Tracker-intro" alt="" src="http://selfcaretracker.com/SCT-intro.png" /></p>
		<table border="0">
		<tbody>
		<tr>
		<td></td>
		<td>
		<p style="font-size: 1.5em; font-weight: normal; text-align: left;">The Self-Care Tracker is a powerful tool to help you maintain your sobriety, succeed in recovery long-term, and improve the overall quality of your life one day at a time.</p>
		<p style="font-size: 1.5em; font-weight: normal; text-align: left;">Setting up your own personal Self-Care Tracker is easy, click on the "Get Started" button to start tracking now!</p>
		[login-form redirect="tracker-settings"]
		[intro-disable]</td>
		</tr>
		</tbody>
		</table>'));
	//echo print_r($page_array,true);
	foreach($page_array as $post_data){
		$post = array(
				'post_author'    => 1,
				'post_content'   => $post_data[2],
				'post_excerpt'   => '',
				'post_name'      => $post_data[0],
				'post_status'    => 'publish',
				'post_title'     => $post_data[1],
				'post_type'      => 'page'
		);
		if(!get_page_by_path($post_data[0])){
			$post_id = wp_insert_post( $post );
			//$message = 'Created custom plugin page : <a href="'.get_permalink($post_id).'" target="_blank">'.$post_data[0].'</a><br />';
			//trigger_error($message,E_USER_ERROR);
		} else {
			//echo 'Page : '.$post_data[0].' Exists Already.<br>';
		}
		if($post_data[0]=='tracker-intro'){ //intro page
			//sethomepage
		}
	}
	//add the custom pages    
	// flush the rewrite rules on activation
	//global $wp_rewrite;
	//add_filter('rewrite_rules_array', 'selfcaretracker_rewrites');
	//$wp_rewrite->flush_rules();
}
function selfcaretracker_deactivation() {
	// Remove the rewrite rule on deactivation
	//global $wp_rewrite;
	//$wp_rewrite->flush_rules();
}
register_activation_hook("$sct_plugin_path/selfcaretracker.php", 'selfcaretracker_activation');
register_deactivation_hook("$sct_plugin_path/selfcaretracker.php", 'selfcaretracker_deactivation');
?>