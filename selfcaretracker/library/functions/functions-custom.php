<?php
remove_filter('the_title', 'wptexturize');
remove_filter('the_content', 'wptexturize');
remove_filter('the_excerpt', 'wptexturize');

require(plugin_dir_path(__FILE__).'../calendar/sct_calendar.php');


//initialize the custom template for the WP simple booking calendar
function sct_show_calendar(){

	sct_get_calendar();

}

add_action( 'init', 'css_header_cookies',1 );
function css_header_cookies() {
    setcookie('nonce', wp_create_nonce('selfcare'));
	global $current_user, $displayed_user_ID, $date, $wpdb;
	$cstm_user_can_access = 0;
	if(isset($_REQUEST['user_id'])){
	    $user_id = $_REQUEST['user_id'];
        $check_frnd_connect_query = $wpdb->get_results("Select * from ".$wpdb->prefix."frnd_connect Where (sender_id = ".get_current_user_id()." AND receiver_id = $user_id) OR (sender_id = $user_id AND receiver_id = ".get_current_user_id().") AND is_friend = 1 ");
        foreach ($check_frnd_connect_query as $frnd_lst){
            if($frnd_lst->sender_id == $user_id && $frnd_lst->sender_share_tracker == 1){
                $cstm_user_can_access = 1;
            }else if ($frnd_lst->receiver_id == $user_id && $frnd_lst->receiver_share_tracker == 1){
                $cstm_user_can_access = 1;
            }
    }
    }

	// import passed id and check viewing permissions
	
	if(@$_REQUEST['user_id']){
		if($current_user->data->ID !== $_REQUEST['user_id'] && !current_user_can('administrator') && $cstm_user_can_access === 0){
		 die(__('You do not have permission to view this data','selfcare'));   
		}
		$user_id = @$_REQUEST['user_id'];
		$displayed_user_ID = $user_id;
	}

	setcookie('displayed_user_ID', $displayed_user_ID);
}


add_action( 'wp_head', 'css_header_raw' );
function css_header_raw() {
	//style the currently tracking date in the calendar
	if(@$_GET['date']){
		
		$tracking_day = date('j',strtotime($_GET['date']));
		//echo '/*'.$_GET['date'].'*/';
		//echo '/*'.$tracking_day.'*/';
		$td_output = '#sbc-calendar td..sbc-day'.$tracking_day.' { border-color:#FFF500 !important;}';
	} else {
		$tracking_day = date('j');
		$td_output = '#sbc-calendar td..sbc-day'.$tracking_day.' { border-color:#FFF500 !important;}';
	}
	echo '<style type="text/css">
		'.$td_output.'

	</style>
	';

}


add_action( 'wp_head', 'js_header_raw', 1000 );
function js_header_raw() {  
	global $sct_plugin_path, $sct_plugin_url;

	echo "
	<script type=\"text/javascript\" id=\"functions-custom-js_header_raw\">

		jQuery.noConflict();
		$ = jQuery;


		//User Interface
		jQuery(document).ready(function(){

			jQuery( \".datepicker\" ).datepicker({ dateFormat: \"yy-mm-dd\" });
			
			//jQuery('#tracker-nav-help').addClass('active');
			//window.parent.$(\"body\").animate({scrollTop:0}, 'fast');

			jQuery('#disableIntro').mousedown(function() {
				if (!jQuery(this).is(':checked')) {
					jQuery.ajax({
					  type: 'POST',
					  data: 'data=disableIntro',
					  success: function() { //alert('success') 
					  },
					  error: function(){ //alert('error:') 
					  },
					  url: '".get_bloginfo('url').'/api/trackersubmit/disableintro/'."',
					  cache:false 
					});
					return false; // prevent default
					jQuery(this).trigger(\"change\");
				} else {
					jQuery.ajax({
					  type: 'POST',
					  data: 'data=enableIntro',
					  success: function() { //alert('success') 
					  },
					  error: function(){ //alert('error:') 
					  },
					  url: '".get_bloginfo('url').'/api/trackersubmit/disableintro/'."',
					  cache:false
					});
					return false; // prevent default
					jQuery(this).trigger(\"change\");		
				}
			});
		  
		});	  
	</script>";

}


add_action( 'template_redirect', 'sct_intro_redirect' );

function sct_intro_redirect() {
	global $current_user;
	$user_id = $current_user->ID;
	$meta_key = 'sc_intro_disable';
	$disable_intro = get_user_meta( $user_id, $meta_key, true);
	//print_r($disable_intro);exit;
	if($disable_intro=="true" && is_page('tracker-intro')){wp_redirect('tracker/');exit;}
	//add hide intro screen into page content
}


add_shortcode( 'intro-disable', 'sct_intro_disable' );
function sct_intro_disable(){
	global $current_user; 
	$user_id = $current_user->ID;
	$meta_key = 'sc_intro_disable';
	$disable_intro = get_user_meta( $user_id, $meta_key, true);
	//add checkbox to page footer
	if($disable_intro=="true"){$checked = ' checked="checked"';}
	$output = '<label style="clear:both;float:right" class="introscreen">Hide intro screen next time? <input type="checkbox" id="disableIntro"'.$checked.' /></label>';
	return $output;
}


	  
add_shortcode( 'login-form', 'my_login_form_shortcode' );
/**
 * Displays a login form.
 *
 * @since 0.1.0
 * @uses wp_login_form() Displays the login form.
 */
function my_login_form_shortcode( $atts, $content = null ) {
	
	$defaults = array(		"redirect"				=>	site_url( $_SERVER['REQUEST_URI'] )
						);

		extract(shortcode_atts($defaults, $atts));
		$redirect = site_url( ).'/tracker/';
		if (!is_user_logged_in()) {
		$content = '<h3>Login to get started</h3>'.wp_login_form( array( 'echo' => false, 'redirect' => $redirect ) );
		} else {
		$content = '<button id="getStartedButton" onclick="window.location=\''.get_bloginfo('url').'/tracker/\'">Get Started &raquo;</button><br><br>';
		}
	return $content;
}


function remove_privates($data){
	return str_replace('Private: ','',$data);
}
add_filter('the_title','remove_privates',1,1000);


//function to retrieve days of abstinence tracked within a date range
function get_abstinent_days($where){
	global $display_days,$userID;
	//echo $where.$user_id.$days;
    //posts in the last 30 days
    $where = " AND post_date > '" . date('Y-m-d', strtotime('-'.$display_days.' days')) . "'";
	$where .= " AND post_title LIKE '%abstinence%' AND post_status = 'publish' AND post_excerpt = 'yes' AND post_author = '{$userID}'";
	//posts  30 to 60 days old
    //$where .= " AND post_date >= '" . date('Y-m-d', strtotime('-60 days')) . "'" . " AND post_date <= '" . date('Y-m-d', strtotime('-30 days')) . "'";
    //posts for March 1 to March 15, 2009
    return $where;
  }
function abstinent_days($user_id, $days){
	global $wp_query, $display_days, $userID;
	$display_days = $days;
	$userID = $user_id;
	$args = array(
		'post_author'=>$user_id,
		'post_type'=>'tracked_ritual',
		'posts_per_page'=>$days
	);
	//echo '<pre>'.print_r($args,true).'</pre>';

	
	$posts = new WP_Query($args);
	add_filter('posts_where', 'get_abstinent_days');
	//add_filter( $tag, $function_to_add, $priority, $accepted_args );
	$posts = query_posts($posts->query_vars);
	remove_filter('posts_where','get_abstinent_days' );
	//echo '<pre>'.print_r($posts ,true).'</pre>';exit;
	$count = count($posts);
	if(!$count)$count=0;
	return $count;
}

//output into function
function output_progress_widget($user_id) {

		//now
		$now = current_time('timestamp');
		//check setupdate and disable any previous days
		$meta_key = 'sc_setupdate';
		$setupDate = get_user_meta( $user_id, $meta_key, true );	
		
		//get number of days between
		$days_offset = round( abs( $now - $setupDate ) / 86400 );		
				
				
		//echo "days:".$days_offset.'<br>';
		//echo "setupDate:".$setupDate.'<br>';
		//echo "now:".$now.'<br>';
		$days_offset++;
		if($days_offset<7){$week = $days_offset;}else{$week=7;}
		if($days_offset<30){$month = $days_offset;}else{$month=30;}
		if($days_offset<356){$year = $days_offset;}else{$year=356;}
		
		echo '<table id="success-table"><thead><tr>';
			echo '<th>'.__('Past Week', 'selfcare').'</th>';
			echo '<th>'.__('Past Month', 'selfcare').'</th>';
			echo '<th>'.__('Past Year', 'selfcare').'</th></tr></thead><tbody><tr class="progress_totals">';
			echo '<td>'.abstinent_days($user_id, 7).' / '.$week.'<br><small>days</small></td>';
			echo '<td>'.abstinent_days($user_id, 30).' / '.$month.'<br><small>days</small></td>';
			echo '<td>'.abstinent_days($user_id, 356).' / '.$year.'<br><small>days</small></td>';
		echo '</tbody></table>';
}





function sc_verify_nonce(){
	$nonce = $_COOKIE['nonce'];
	$nonce_valid = wp_verify_nonce( $nonce, $selfcare ); 
	setcookie('nonce', wp_create_nonce('selfcare'));
	if($nonce_valid) {
		return true;
	} else {
		return false;
	}

}

// for your eyes only
function posts_for_current_author($query) {
	global $user_level;

	if($query->is_admin && $user_level < 5) {
		global $user_ID;
		$query->set('author',  $user_ID);
		unset($user_ID);
	}
	unset($user_level);

	return $query;
}
//add_filter('pre_get_posts', 'posts_for_current_author'); 


// ADD NEW ADMIN COLUMN FOR USERS to show tracked rituals

add_filter('manage_users_columns', 'custom_columns_head');
add_action('manage_users_custom_column', 'custom_columns_content', 10, 3);

function abstinence_filter( $where = '' ) {
    global $wpdb;
    $where .= " AND post_title LIKE '%Abstinence%'";
    return $where;
}

 function custom_columns_head($columns) {
	 $columns['date_joined'] = 'Date Joined:';
	 $columns['tracked_days'] = 'Tracked Days';
	 $columns['view'] = 'View Tracker as:';
	return $columns;
}

function custom_columns_content($value='', $column_name, $user_id) {
	if ($column_name == 'tracked_days') {
		add_filter( 'posts_where', 'abstinence_filter' );		
		$tracked_days = get_posts( array( 
			'post_type' => 'tracked_ritual', 
			'post_status' => 'publish', 
			'author'    => $user_id, 
			'nopaging'  => true, // display all posts
			'suppress_filters' => false
		) );
		remove_filter( 'posts_where', 'abstinence_filter' );
		wp_reset_query();		
		return count($tracked_days);

	}
	if( $column_name == 'view' ) {
			$user_data = get_userdata($user_id);
			$display_name = $user_data->display_name;
			//the link to view as this user
			$url = get_bloginfo('url').'/tracker-stats/?user_id='.$user_id;
			$link = '<a href="'.$url.'">'.$display_name.'</a>';
			return $link;

    }
	if( $column_name == 'date_joined' ) {
		$unix_join_date = get_user_meta( $user_id, 'sc_setupdate', true );
		if($unix_join_date)
			$date_joined = date('Y-m-d', $unix_join_date);
		$url = get_bloginfo('url').'/wp-admin/user-edit.php?user_id='.$user_id.'&wp_http_referer='.urlencode($_SERVER['PHP_SELF']).'#sct_user_options';
		$link = '<a href="'.$url.'">'.$date_joined.'</a>';
		return $link;
	}
}


add_action( 'edit_user_profile', 'sct_user_joined_date' );
add_action( 'profile_personal_options', 'sct_user_joined_date' );

function sct_user_joined_date( $user ) { ?>
	<h3 id="sct_user_options">Self Care Tracker</h3>
	<table class="form-table">
		<tr>
			<th><label>SCT Join Date</label></th>
			<td>
				<input style="width: 90px;padding: 6px;font-weight: bold;margin-top: -5px;" placeholder="YYYY-MM-DD" type="text" name="sc_setupdate" id="sc_setupdate_dp" value="<?php echo date('Y-m-d',  get_user_meta( $user->ID, 'sc_setupdate', true )); ?>" class="datepicker" />
				<input type="hidden" name="sc_setupdate_unix" value="<?php echo get_user_meta( $user->ID, 'sc_setupdate', true ); ?>" class="regular-text" /></td>
		</tr>
	</table>
<?php
}

add_action( 'edit_user_profile_update', 'sct_save_user_joined_date' );
add_action( 'personal_options_update', 'sct_save_user_joined_date' );

function sct_save_user_joined_date( $user_id )
{
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

	$unix_time = strtotime( $_POST['sc_setupdate'])+86399;

	if($unix_time)
		update_user_meta( $user_id, 'sc_setupdate', $unix_time );
}



add_action('template_redirect','sct_redirect_tracker_intro');
function sct_redirect_tracker_intro(){
	global $current_user; 
	$user_id = $current_user->ID;
	$meta_key = 'sc_intro_disable';
	$disable_intro = get_user_meta( $user_id, $meta_key, true);
	if($disable_intro=="true" && is_page('tracker-intro')){wp_redirect(site_url().'/tracker/');exit;}
}

