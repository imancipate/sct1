<?php


function filter_progress_where( $where = '' ) {
	// set displayed user
	global $wpdb, $wp_query, $current_user, $displayed_user_ID;
	
	
	if(!$displayed_user_ID){ 
		$displayed_user_ID = $current_user->ID; 
	}
	else
	{
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

		//check permissions
		if($displayed_user_ID != $current_user->ID && !current_user_can('administrator') && $cstm_user_can_access == 0){
			//no permission
			die('You do not have permission to view this data');
		}
	}

	$currentdate = date('Y-m-d',current_time('timestamp',1));
	$startdate = strtotime ( '-7 day' ) ;
	$startdate = date ( 'Y-m-d' , $startdate );

	if(@$_REQUEST['startdate']){
		$strDateFrom = $_REQUEST['startdate'];		
	} else {
		$strDateFrom =  $startdate; 
	}

	if(@$_REQUEST['enddate']){
		$strDateTo = $_REQUEST['enddate'];		
	} else {
		$strDateTo =  $currentdate;
	}
	
	if($strDateTo==$strDateFrom){
		$where = " AND post_date = '{$strDateFrom}'";
	} else {
		// posts for March 1 to March 15, 2010
		$where = " AND post_date >= '{$strDateFrom}' AND post_date <= '{$strDateTo}'";
	}
	$where .= " AND post_author = '{$displayed_user_ID}'";
	
	$where .= " AND post_status = 'publish'";
	
	$where .= " AND (post_type = 'tracked_ritual' OR post_type = 'journal_entry')";
	
	

	//echo "where: ".$where;
	


	return $where;
}	





function createDateRangeArray($strDateFrom,$strDateTo)
{
    // takes two dates formatted as YYYY-MM-DD and creates an
    // inclusive array of the dates between the from and to dates.

    // could test validity of dates here but I'm already doing
    // that in the main script
	$currentdate = date('Y-m-d',current_time('timestamp',1));
	$startdate = strtotime ( '-7 day' ) ;
	$startdate = date ( 'Y-m-d' , $startdate );

	//echo "date_default_timezone_get: ".date_default_timezone_get();
	
	if(@$_REQUEST['startdate']){
		$strDateFrom = $_REQUEST['startdate'];		
	} else {
		$strDateFrom =  $startdate; 
	}

	if(@$_REQUEST['enddate']){
		$strDateTo = $_REQUEST['enddate'];		
	} else {
		$strDateTo =  $currentdate;
	}
	
	
    $aryRange=array();
	//var_dump($strDateFrom);die();
    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

    if ($iDateTo>=$iDateFrom)
    {
        //array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
		$aryRange[date('Y-m-d',$iDateFrom)] = '';
        while ($iDateFrom<$iDateTo)
        {
            $iDateFrom+=86400; // add 24 hours
            //array_push($aryRange,date('Y-m-d',$iDateFrom));
			$aryRange[date('Y-m-d',$iDateFrom)] = '';
       }
    }
	krsort($aryRange);
    return $aryRange;
}

function day_is_tracked($date, $displayed_user_ID){
//check if anything is tracked that day
	global $wpdb;
	
	$displayed_user_ID = esc_sql($displayed_user_ID);
	//mysqli_real_escape_string($displayed_user_ID);
	$date = esc_sql($date);
	//mysqli_real_escape_string($date);

	$sql = $wpdb->prepare( "SELECT ID, post_content FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND post_title NOT LIKE %s AND post_date = %s " , $displayed_user_ID, 'tracked_ritual', '%abstinence%', $date);
	
	//$sql = "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_author = '{$displayed_user_ID}' AND post_type = 'tracked_ritual' AND post_title NOT LIKE '%abstinence%' AND post_date = '{$date}'";
	//echo $sql; exit;
	$is_tracked = $wpdb->get_results( $sql, ARRAY_A );
	if(!empty($is_tracked)){
		//echo '<pre>'.print_r($is_tracked, true).'</pre>';
		return $is_tracked;
	} else {
		return false;
	}
	//echo '<pre>'.print_r($is_tracked, true).'</pre>';
}
function abstinence_tracked($date, $displayed_user_ID){
//check if anything is tracked that day
	global $wpdb;
	
	$displayed_user_ID = esc_sql($displayed_user_ID);//mysqli_real_escape_string($displayed_user_ID);
	$date = esc_sql($date);//mysqli_real_escape_string($date);
	
	$sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND post_title NOT LIKE %s AND post_date = %s " , $displayed_user_ID, 'tracked_ritual', '%abstinence%', $date);
	//$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_author = '{$displayed_user_ID}' AND post_type = 'tracked_ritual' AND post_title LIKE '%abstinence%' AND post_date = '{$date}'";
	//echo $sql; exit;
	$is_tracked = $wpdb->get_results( $sql, ARRAY_A );
	if(!empty($is_tracked)){
		//echo '<pre>'.print_r($is_tracked, true).'</pre>';
		return $is_tracked;
	} else {
		return false;
	}
	//echo '<pre>'.print_r($is_tracked, true).'</pre>';
}

function day_class($date, $displayed_user_ID){
//check if anything is tracked that day
	global $wpdb;

	$displayed_user_ID = esc_sql($displayed_user_ID);//mysqli_real_escape_string($displayed_user_ID);
	$date = esc_sql($date);//mysqli_real_escape_string($date);
	//echo '<pre>'.print_r($date, true).'</pre>';
	//echo '<pre>'.print_r($displayed_user_ID, true).'</pre>';

	//get the count of YES responses for this day
	$sql = $wpdb->prepare( "SELECT count(*) as daycount FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND post_title NOT LIKE %s AND post_excerpt = %s AND post_date = %s" , $displayed_user_ID, 'tracked_ritual', '%abstinence%', 'yes', $date);
	//$sql = "SELECT count(*) as daycount FROM {$wpdb->posts} WHERE post_author = '{$displayed_user_ID}' AND post_type = 'tracked_ritual' AND post_title NOT LIKE '%abstinence%' AND post_excerpt = 'yes' AND post_date = '{$date}'";
	//echo $sql;
	$yescount = $wpdb->get_row($sql, ARRAY_A );

	//get the count of YES responses for this day
	$sql = $wpdb->prepare( "SELECT count(*) as daycount FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND post_title NOT LIKE %s AND post_excerpt = %s AND post_date = %s" , $displayed_user_ID, 'tracked_ritual', '%abstinence%', 'no', $date);
	//$sql = "SELECT count(*) as daycount FROM {$wpdb->posts} WHERE post_author = '{$displayed_user_ID}' AND post_type = 'tracked_ritual' AND post_title NOT LIKE '%abstinence%' AND post_excerpt = 'no' AND post_date = '{$date}'";
	$nocount = $wpdb->get_row($sql, ARRAY_A );

	//get the count of NO responses for this day
	$sql = $wpdb->prepare( "SELECT count(*) as abstinence FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND post_title LIKE %s AND post_excerpt = %s AND post_date = %s" , $displayed_user_ID, 'tracked_ritual', '%abstinence%', 'no', $date);
	//$sql = "SELECT count(*) as abstinence FROM {$wpdb->posts} WHERE post_author = '{$displayed_user_ID}' AND post_type = 'tracked_ritual' AND post_title LIKE '%abstinence%' AND post_excerpt = 'no' AND post_date = '{$date}'";
	//echo $sql;
	$abstinence = $wpdb->get_row($sql, ARRAY_A );

	//echo '<pre>' . $date . "-" .print_r($abstinence, true).'</pre>';


	if(!empty($yescount['daycount'])||!empty($nocount['daycount'])){
		//echo '<pre>'.print_r($is_tracked, true).'</pre>';

		//echo("<h1>YES COUNT: {$yescount['daycount']}</h1>");

		$y = $yescount['daycount'];
		if ( $y < 1 ) {
			$count_css = 'sct-bggray';
		} else if ( $y <= 2 ) {
			$count_css = 'sct-bgred';
		} else if ( $y <= 4 ) {
			$count_css = 'sct-bgorange';
		} else if ( $y <= 7 ) {
			$count_css = 'sct-bggreen';
		} else {
			$count_css = "sct-bggreen";
		}

		//@ TODO: Add matrix to this section for user defined colors and ranges
		// switch($yescount['daycount']){
		// 	case '':
		// 	case 0:
		// 		$count_css = 'sct-bggray';
		// 	break;
		// 	case 1:
		// 	case 2:
		// 		$count_css = 'sct-bgred';
		// 	break;
		// 	case 3:
		// 	case 4:
		// 		$count_css = 'sct-bgorange';
		// 	break;
		// 	case 5:
		// 	case 6:
		// 	case 7:
		// 		$count_css = 'sct-bggreen';
		// 	break;
		// 	default:
		// 		$count_css = 'sct-bggreen';
		// 	break;
		// }
		//echo "<h1>{$abstinence['abstinence']}</h1>";
		// if(!empty($abstinence['abstinence']))
		if(!empty($abstinence['abstinence']))
			$count_css = 'sct-bggray';
		return $count_css;
	} else {
		return 'sct-status-free';
	}
	//echo '<pre>'.print_r($is_tracked, true).'</pre>';
}



add_action('wp_ajax_get_stats', 'get_stats');

function get_stats() {
	global $wpdb; // this is how you get access to the database

	die(); // this is required to return a proper result
}

add_action('wp_ajax_deleteDay', 'deleteDay');

function deleteDay($post_parent='') {
	global $wpdb, $current_user; // this is how you get access to the database
		
		
		if(@$_REQUEST['user_id']){
			if($current_user->data->ID != @$_REQUEST['user_id'] && !current_user_can('administrator'))
				die(__('You do not have permission to delete this data','selfcare'));
			//$user_id = @$_REQUEST['user_id'];
			//$displayed_user_ID = $user_id;
			//$displayed_user_data = get_userdata($displayed_user_ID);
		}

		if(!$post_parent)
			$post_parent = esc_sql(@$_REQUEST['post_parent']);//mysqli_real_escape_string (@$_REQUEST['post_parent']);
		//get the associated posts
		//print_r(@$_REQUEST);
		
		if($post_parent=='')
			die(__('No post id','selfcare'));
		
		//echo 'deleting post parent:'.$post_parent;
		
		
		$user_id = esc_sql(@$_REQUEST['user_id']);//mysqli_real_escape_string (@$_REQUEST['user_id']);
		$date = esc_sql(@$_REQUEST['postdate']);//mysqli_real_escape_string (@$_REQUEST['postdate']);
		$sql = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent= %d ", $post_parent);
		//$sql = "SELECT ID FROM $wpdb->posts WHERE post_parent='".$post_parent."'";
		//echo $sql;
		$rel_posts = $wpdb->get_results($sql);
		//add the parent post
		$parent_post = get_post($post_parent);
		$rel_posts[] = $parent_post;
		//fire away!
		foreach($rel_posts as $post){
		
			$postid = $post->ID;
			$deleted = wp_delete_post( $postid );
			//if($deleted)
				//echo $postid .' deleted:<br>';
		
		}
		return true;
		//print_r($rel_posts);		
		
	die(); // this is required to return a proper result
}


function jquery_startup_functions() {
	global $sct_plugin_path, $sct_plugin_url;

	echo '
	<script type="text/javascript">
		/* moved to js file for now */
		var ajaxurl = "'.home_url("wp-admin/admin-ajax.php").'";
		var user_id = "'.$displayed_user_ID.'";
		function get_stats(startdate, enddate, userid){

			$(\'#sct-cal-ajax-spinner\').show();
			var data = {
				action: \'get_stats\',
				startdate: startdate,
				enddate: enddate,
				user_id: userid
			};
			//is this on the tracker page?
			if(jQuery("#stats-inner").length == 0){
				//redirect to the stats page
				window.location=\''.get_bloginfo('url').'/tracker-stats/?startdate=\'+startdate+\'&enddate=\'+enddate+\'&user_id=\'+userid+\'#tracker-title-menu\';
				
			} else {
				//update the contents with ajax
				jQuery.post(\''.get_bloginfo('url').'/api/progressajax/getprogress/\', data, function(response) {
					jQuery(\'#stats-inner\').html(response);
					$(\'#sct-cal-ajax-spinner\').hide();
				});
				jQuery(this).parent().parent().css(\'background-color\', \'red\');
				window.location=\'#tracker-title-menu\';
				jQuery(\'.stats_view_tab.active\').removeClass(\'active\');
				jQuery(\'#stats_tab_showall\').addClass(\'active\');

			}
		}
		
		function deleteDay(parentID, userID, postdate){
			
			if (confirm("'.__('Delete data for "+postdate+" (including journal entry)?', 'selfcare').'")) {
				//var postID = parentID; 
				
				var data = {
					action: \'deleteDay\',
					post_parent: parentID,
					user_id: userID,
					postdate: postdate
				};				
				
				//jQuery(\'#stats-inner\').fadeOut(\'slow\');
				jQuery.post(\''.get_bloginfo('url').'/wp-admin/admin-ajax.php\', data, function(response) {
					//console.log(response);
					if(response){
						alert("'.__('Data deleted - You will now be redirected to the tracker page for ', 'selfcare').'"+postdate);
						window.location=\''.get_bloginfo('url').'/tracker/?date=\'+postdate+\'&user_id=\'+userID+\'\';
					}
				});
				
				
			};
			

		}
	
	</script>
	';
	
	
}

add_action( 'wp_head', 'jquery_startup_functions' );


//define the cookie for viewed use in the header
function setUserCookie(){
	global $current_user, $displayed_user_ID, $user_id, $wpdb;
	$user_id = $current_user->ID;
	
	//echo '<pre>'.print_r($current_user, true).'</pre>';
    $cstm_user_can_access = 0;
    if(isset($_GET['user_id'])){
        $user_id = $_GET['user_id'];
        $check_frnd_connect_query = $wpdb->get_results("Select * from ".$wpdb->prefix."frnd_connect Where (sender_id = ".get_current_user_id()." AND receiver_id = $user_id) OR (sender_id = $user_id AND receiver_id = ".get_current_user_id().") AND is_friend = 1 ");
        foreach ($check_frnd_connect_query as $frnd_lst){
            if($frnd_lst->sender_id == $user_id && $frnd_lst->sender_share_tracker == 1){
                $cstm_user_can_access = 1;
            }else if ($frnd_lst->receiver_id == $user_id && $frnd_lst->receiver_share_tracker == 1){
                $cstm_user_can_access = 1;
            }
        }
    }
	if(@$_GET['user_id']){
		if($user_id != @$_GET['user_id'] && !current_user_can('administrator') && $cstm_user_can_access == 0){
			setcookie ('user_id', '', time() - 3600);
			die(__('You do not have permission to view this data - ajax','selfcare'));
			}
		$displayed_user_ID = @$_GET['user_id'];
		setcookie('user_id', $displayed_user_ID, time()+3600, COOKIEPATH, COOKIE_DOMAIN, false);
	}
	
	//echo '<pre>'.print_r(@$_REQUEST, true).'</pre>';
	//echo 'user'.$displayed_user_ID; exit; 

	}
add_action( 'wp_ajax_calendarNavigation', 'setUserCookie' );

?>