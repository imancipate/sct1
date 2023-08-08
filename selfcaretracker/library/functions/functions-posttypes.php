<?php
add_action('template_redirect','redirect_tracked_day');
function redirect_tracked_day(){
	global $wp_query;
	$tracker_page = $wp_query->query['pagename'];

	if($tracker_page=='tracker'||$tracker_page=='tracker-settings'||$tracker_page=='tracker-stats'||$tracker_page=='tracker-help'){
		//is user logged in?
		if ( !is_user_logged_in() ) {
		   wp_redirect('./tracker-intro');
		}
	}
		
	//tracker page only
	if($tracker_page=='tracker'){
		//is user logged in?
		if ( !is_user_logged_in() ) {
		   wp_redirect('./tracker-intro');
		}
		$user_id = $current_user->ID;

		global $current_user, $displayed_user_ID, $date;
		//get user data
		if($displayed_user_ID){
			$user_id = $displayed_user_ID;
		} elseif(@$_GET['user_id']) {
			$user_id = @$_GET['user_id'];
		} else {
			$user_id = $current_user->ID;
		}
		// get the passed date, or today, if they choose the future
		$date = date('Y-m-d', current_time('timestamp'));

		if($_REQUEST['date']){
			$date = $_REQUEST['date'];
			$inputdate = $date;
			$today = date('Y-m-d', current_time('timestamp'));
				//echo $inputdate.$today;
			if($inputdate > $today){
				//echo 'future';
				$date = date('Y-m-d');
			} 
		}

	//echo '<pre>'.$date.$user_id.print_r($wp_query,true).'</pre>';exit;
	//echo 'trcked: '.$date.' '.$user_id;
		//check if anything is tracked that day
		$is_tracked = day_is_tracked($date, $user_id);
		//echo $date.' - '.$displayed_user_ID; exit;
		if($is_tracked ){
			if($displayed_user_ID){
				$user_link = '&user_id='.$displayed_user_ID;
			} else {
				$user_link = '';
			}
			if($_GET['retrack']==""){
				$message = '&message=tracked';
				$date = esc_sql($date);
				$location = get_bloginfo('url').'/tracker-stats/?startdate='.$date.'&enddate='.$date.$user_link.$message;
				//$location = get_bloginfo('url').'/tracker-stats/?startdate='.mysqli_real_escape_string($date).'&enddate='.mysqli_real_escape_string($date).$user_link.$message;
				wp_redirect($location);
				exit;
			} else {


			}
	
		}
	}
}
add_action('wp_head', 'sct_page_header');
function sct_page_header(){

	global $wp_query;
	//return '<pre>'.print_r($wp_query,true).'</pre>';exit;
	$tracker_page = $wp_query->query['pagename'];
	if($tracker_page!=''){
		switch($tracker_page){
			case 'tracker':		
			case 'tracker-stats':
			case 'tracker-settings':
			case 'tracker-help':
				include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-header.php');
				//include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-tracker-settings.php');
			break;				
			default:			
			break;
		}

		// Returns the content.
	}
	return true;

}


add_filter( 'the_content', 'sct_replace_page_content', 100 );
function sct_replace_page_content($content){
	global $wp_query;
	//return '<pre>'.print_r($wp_query,true).'</pre>';exit;
	$tracker_page = $wp_query->query['pagename'];
	if($tracker_page!=''){
		switch($tracker_page){
			case 'tracker':
				include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-tracker-header.php');
				include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-tracker.php');
				remove_filter( 'the_content', 'sct_replace_page_content');return;
			break;		
			case 'tracker-stats':
				include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-tracker-header.php');
				include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-tracker-stats.php');
				remove_filter( 'the_content', 'sct_replace_page_content');return;
			break;		
			case 'tracker-settings':
				include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-tracker-header.php');
				include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-tracker-settings.php');
				remove_filter( 'the_content', 'sct_replace_page_content');return;
			break;	
			case 'tracker-help':
				include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-tracker-header.php');
				//include(WP_PLUGIN_DIR . '/selfcaretracker/templates/default/page-tracker-settings.php');
				$help_active = '<script type="text/javascript">jQuery(document).ready(function(){jQuery("#tracker-nav-help").addClass("active");});</script>';
				remove_filter( 'the_content', 'sct_replace_page_content');
				return $help_active.$content;
			break;				
			default:			
			break;
		}

		// Returns the content.
	}
	return $content;

}


//add data format
add_theme_support( 'post-formats', array( 'abstinence', 'ritual' ) );

?>