<?php

/* function to load calendar via ajax
*/

add_action( 'wp_ajax_sct_get_ajax_calendar', 'sct_get_ajax_calendar' );

function sct_get_ajax_calendar() {
	global $wpdb; //get access to the database

	sct_get_calendar();

	die(); // this is required to return a proper result
}


/**
 * Get number of days since the start of the week.
 **/

function sct_calendar_week_mod($num) {
	$base = 7;
	return ($num - $base*floor($num/$base));
}

/**
 * Display calendar with days that formatted links and colors
 **/

function sct_get_calendar($initial = true, $echo = true) {
	global $wpdb, $m, $monthnum, $year, $wp_locale, $posts, $displayed_user_ID, $current_user;

	//get the user's setup date
	$meta_key = 'sc_setupdate';
	$setupDate = get_user_meta( $current_user->ID, $meta_key, true );

	//echo 'setupdate:'.$setupDate;




	$cache = array();
	$key = md5( $m . $monthnum . $year );
	if ( $cache = wp_cache_get( 'sct_get_calendar', 'calendar' ) ) {
		if ( is_array($cache) && isset( $cache[ $key ] ) ) {
			if ( $echo ) {
				echo apply_filters( 'sct_get_calendar',  $cache[$key] );
				return;
			} else {
				return apply_filters( 'sct_get_calendar',  $cache[$key] );
			}
		}
	}

	if ( !is_array($cache) )
		$cache = array();


	// Let's figure out when we are
	if ( !empty($monthnum) && !empty($year) ) {
		$thismonth = ''.zeroise(intval($monthnum), 2);
		$thisyear = ''.intval($year);
	} elseif ( !empty($w) ) {
		// We need to get the month from MySQL
		$thisyear = ''.intval(substr($m, 0, 4));
		$d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
		$thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')");
	} elseif ( !empty($m) ) {
		$thisyear = ''.intval(substr($m, 0, 4));
		if ( strlen($m) < 6 )
			$thismonth = '01';
		else
			$thismonth = ''.zeroise(intval(substr($m, 4, 2)), 2);
	} else {
		$thisyear = gmdate('Y', current_time('timestamp'));
		$thismonth = gmdate('m', current_time('timestamp'));
	}

	$weekBegins = intval(get_option('start_of_week'));

	$currentTimestamp = current_time('timestamp');
	//$thisyear = (int) gmdate('Y', ($this->timestamp ? $this->timestamp : $currentTimestamp));
	//$thismonth = (int) gmdate('m', ($this->timestamp ? $this->timestamp : $currentTimestamp));

	if(@$_REQUEST['startdate']){
		$date = $_REQUEST['startdate'];
	}
	if(@$_REQUEST['date']){
		$date = $_REQUEST['date'];
	}
	if($date){
		$thisyear = (int) substr($date, 0, 4);
		$thismonth = (int) substr($date, 5, 2);
	}
	if(!empty($_GET['user_id'])){
		$user_ID = $_GET['user_id'];
		$displayed_user_ID = $_GET['user_id'];
	}
	if(!empty($_REQUEST['monthnum'])){
		$thismonth = (int)$_REQUEST['monthnum'];
	}
	if(!empty($_REQUEST['year'])){
		$thisyear = (int)$_REQUEST['year'];
	}
	if(!empty($_REQUEST['userid'])){
		$user_ID = $_REQUEST['userid'];
		$displayed_user_ID = $_REQUEST['userid'];
		//echo '<br>1user: '.$displayed_user_ID;
	}

	if(!$displayed_user_ID)
		$displayed_user_ID = $current_user->ID;

	//check perms
    $cstm_user_can_access = 0;
    if(isset($_GET['user_id']) || isset($displayed_user_ID)){
        if(isset($_GET['user_id'])){
            $user_id = $_GET['user_id'];
        }else{
            $user_id = $displayed_user_ID;
        }
        $check_frnd_connect_query = $wpdb->get_results("Select * from ".$wpdb->prefix."frnd_connect Where (sender_id = ".get_current_user_id()." AND receiver_id = $user_id) OR (sender_id = $user_id AND receiver_id = ".get_current_user_id().") AND is_friend = 1 ");
        foreach ($check_frnd_connect_query as $frnd_lst){
            if($frnd_lst->sender_id == $user_id && $frnd_lst->sender_share_tracker == 1){
                $cstm_user_can_access = 1;
            }else if ($frnd_lst->receiver_id == $user_id && $frnd_lst->receiver_share_tracker == 1){
                $cstm_user_can_access = 1;
            }
        }
    }
	$checkid = $current_user->ID;
	if($checkid != $displayed_user_ID && !current_user_can('administrator') && $cstm_user_can_access == 0)
		die(__('You do not have permission to view this data','selfcare'));




	//print_r($_REQUEST);

	$viewingmonth = $thismonth;


	$unixMonth = mktime(0, 0, 0, $thismonth, 1, $thisyear);
	$daysinmonth = intval(date('t', $unixMonth));
	$last_day = date('t', $unixMonth);




	//@TODO - Add user-defined options to allow for customized timezones and calendar formats


	// Get the next and previous month and year with at least one post
	$previous = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date < '$thisyear-$thismonth-01'
		AND post_type = 'tracked_ritual' AND post_status = 'publish' AND post_author = {$displayed_user_ID} AND post_title LIKE '%abstinence%'
			ORDER BY post_date DESC
			LIMIT 1");
	$next = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date > '$thisyear-$thismonth-{$last_day} 23:59:59'
		AND post_type = 'tracked_ritual' AND post_status = 'publish' AND post_author = {$displayed_user_ID} AND post_title LIKE '%abstinence%'
			ORDER BY post_date ASC
			LIMIT 1");


	/* translators: Calendar caption: 1: month name, 2: 4-digit year */
	$calendar_caption = _x('%1$s %2$s', 'calendar caption');



	$calendar_output = '<!-- start calendar --><div class="sct-calendar-month">';

	$calendar_output .= '	<table class="sct-calendar">

	<caption>' . sprintf($calendar_caption, $wp_locale->get_month($thismonth), $thisyear) . '<div id="sct-cal-ajax-spinner"></div></caption>
	<thead><tr>';

	$prevmonth = date('m',strtotime(date("Y-m-d", strtotime($thisyear.'-'.$thismonth.'-01 -1 month'))));
	$nextmonth = date('m',strtotime(date("Y-m-d", strtotime($thisyear.'-'.$thismonth.'-01 +1 month'))));
	$prevyear = date('Y',strtotime(date("Y-m-d", strtotime($thisyear.'-'.$thismonth.'-01 -1 month'))));
	$nextyear = date('Y',strtotime(date("Y-m-d", strtotime($thisyear.'-'.$thismonth.'-01 +1 month'))));


	$ajax_prev = '$(\'#sct-cal-ajax-spinner\').show();$.post(ajaxurl, {action:\'sct_get_ajax_calendar\',monthnum:\''.$prevmonth.'\',year:\''.$prevyear.'\',userid:\''.$displayed_user_ID.'\'}, function(response) { $(\'#sct-calendar-wrapper\').html(response);	});return false;';
	$ajax_next = '$(\'#sct-cal-ajax-spinner\').show();$.post(ajaxurl, {action:\'sct_get_ajax_calendar\',monthnum:\''.$nextmonth.'\',year:\''.$nextyear.'\',userid:\''.$displayed_user_ID.'\'}, function(response) { $(\'#sct-calendar-wrapper\').html(response);	});return false;';


	$calendar_output .= "\n\t\t".'<th colspan="4" id="prev"><a href="#" onclick="'.$ajax_prev.'" title="View Previous Month">&laquo; ' . 'Prev Month' . '</a></td>';

	$calendar_output .= "\n\t\t".'<th colspan="4" id="next"><a href="#" onclick="'.$ajax_next.'" title="View Next Month">' . 'Next Month' . ' &raquo;</a></td>';

	$calendar_output .= '
	</tr>

	<tr>';

	$myweek = array();

	for ( $wdcount=0; $wdcount<=6; $wdcount++ ) {
		$myweek[] = $wp_locale->get_weekday(($wdcount+$week_begins)%7);
	}

	foreach ( $myweek as $wd ) {
		$day_name = (true == $initial) ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
		$wd = esc_attr($wd);
		$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
	}

	$calendar_output .= '
	<th></th></tr>

	</thead>

	<tbody>
	<tr>';


	// See how much we should pad in the beginning
	$pad = sct_calendar_week_mod(date('w', $unixMonth)-$week_begins);
	if ( 0 != $pad )
		$calendar_output .= "\n\t\t".'<td colspan="'. esc_attr($pad) .'" class="pad">&nbsp;</td>';

	$daysinmonth = intval(date('t', $unixMonth));


	$currentTimestamp = current_time('timestamp');




	//echo 'dim: '.$daysinmonth;
	//echo 'newrow: '.$newrow;

	for ( $day = 1; $day <= $daysinmonth+1; ++$day ) {


		$strdate = $thisyear.'-'.sprintf ("%02u", $thismonth).'-'.sprintf ("%02u", $day);

		//check if future date
		if(strtotime($strdate)>$currentTimestamp){
			$future_date = true;
		} else {
			$future_date = false;
			if($day == $daysinmonth+1){
				$thisdate = $thisyear.'-'.sprintf ("%02u", $thismonth).'-'.sprintf ("%02u", $day-1);
				if(strtotime($thisdate)>$currentTimestamp){
					$future_date = true;
				}
			}
		}




		//first week?
		if($day<9 && true == $newrow	){
			$weekstart=$weekend='';
			$weekstart = date('Y-m-d',strtotime(date("Y-m-d", strtotime($thisyear.'-'.$thismonth.'-'.$day. " -7 days"))));
			$weekend = date('Y-m-d',strtotime(date("Y-m-d", strtotime($weekstart)) . " +6 days"));

			if(!$future_date){
			//put week view icon in for first week
			$calendar_output .= '<td class="week_view_column '.$thisdate .'"><a class="week_view" title="Click to view progress this week" onclick="get_stats(\''.$weekstart.'\',\''.$weekend.'\''.$user_link.');jQuery(\'.currentrow\').removeClass(\'currentrow\');jQuery(\'.sct-current\').removeClass(\'sct-current\');jQuery(this).parent(\'td\').parent(\'tr\').addClass(\'currentrow\');return false;"></a>';
			}
		}


		if (!empty($newrow)&&$day<$daysinmonth+1){


			$weekstart=$weekend='';
			$weekstart = date('Y-m-d',strtotime(date("Y-m-d", strtotime($thisyear.'-'.$thismonth.'-'.$day))));
			$weekend = date('Y-m-d',strtotime(date("Y-m-d", strtotime($weekstart)) . " +6 days"));



			//echo '<br>day: '.$day.' ws:'.$weekstart;
			//echo '<br>day: '.$day.' we:'. $weekend;

			if(!$future_date){
				if($weekstart){
					if(!$future_date || strtotime(date("Y-m-d", strtotime($weekend)) . " -6 days")<=$currentTimestamp){
						$endrowicon = '<td class="week_view_column 2"><a class="week_view" title="Click to view progress this week" onclick="get_stats(\''.$weekstart.'\',\''.$weekend.'\''.$user_link.');jQuery(\'.currentrow\').removeClass(\'currentrow\');jQuery(\'.sct-current\').removeClass(\'sct-current\');jQuery(this).parent(\'td\').parent(\'tr\').addClass(\'currentrow\');return false;"></a>';
					}
				}
			}
			//$weekstart = $strdate;
			if($weekstart == @$_GET['startdate'] && $weekstart != @$_GET['enddate']){
				$currentrow='currentrow';
			}else {
				$currentrow='';
			}


			if($day<$daysinmonth+1){
				$calendar_output .= "\n\t</tr>\n\t<tr class=\"{$currentrow}\">\n\t\t";
			}
			$newrow = false;

		}



		if($day<$daysinmonth+1){

			//global $displayed_user_ID;
			if($displayed_user_ID){
				$user_link = ', \''.$displayed_user_ID.'\'';
			} else {
				$user_link = '';
			}


			//echo '<br>strdate: '.$strdate;



			$datecal = $thisyear.'-'.$thismonth.'-'.$day;

			$date = date('Y-m-d',strtotime($strdate));

			//echo '<br>date: '.$date;
			//echo '<br>user: '.$displayed_user_ID;



			$isToday = ($day == gmdate('j', $currentTimestamp) && $thismonth == gmdate('m', $currentTimestamp) && $thisyear == gmdate('Y', $currentTimestamp));


			if($displayed_user_ID){
				$user_link = ', \''.$displayed_user_ID.'\'';
			} else {
				$user_link = '';
			}



			//get cklass for tracked / vs / free days
			$day_class = day_class($date, $displayed_user_ID);
			if ($day_class == 'sct-status-free'){

				if($displayed_user_ID){
					$user_query = '&user_id='.$displayed_user_ID;
				}
				$track_link = get_bloginfo('url').'/tracker/?date='.$date.$user_query.'#tracker-title-menu';
				if($current_user->ID != $displayed_user_ID){
                    $track_link = "#";
                }

				$title_text = __('Click to Track your actions for this day','selfcare');
                if($current_user->ID != $displayed_user_ID){
                    $track_link = "#";
                    $title_text = __("You can't track for this day",'selfcare');
                }
                $day_onclick = 'window.location=\''.$track_link.'\';';
			}else{
				$day_class = $day_class.' sct-tracked';
				$title_text = __('Click to view progress for this day','selfcare');
				$day_onclick = "jQuery('.currentrow').removeClass('currentrow');jQuery('.sct-current').removeClass('sct-current');jQuery(this).addClass('sct-current');get_stats('".$strdate."','".$strdate."'".$user_link.");";
			}

			if($future_date)
			{
				$title_text = __('You cannot track future dates','selfcare');
				$day_onclick = "jQuery(this).effect('highlight', {'color':'red'}, 300);";
				$day_class = ' sct-day-future';
			}

			//check setupdate and disable any previous days
			$future_style = '';
			if(strtotime($strdate)<($setupDate-86400)) //before setup date
			{
				$title_text = __('You cannot track dates before you signed up','selfcare');
				$day_onclick = "jQuery(this).effect('highlight', {'color':'red'}, 300);";
				$day_class = ' sct-day-past';
			}

			//cell is today
			if ($isToday){
				$day_class = $day_class.' sct-today';
				$title_text = __('Click to Track your actions Today','selfcare');
			}


			//tracking this day
			$trackingNow = '';
			if($_REQUEST['date']){
				if($_REQUEST['date']==$strdate){
					$day_class = ' sct-day-tracking';
					$title_text = "You are currently tracking this day";
				}
			}


			$calendar_output .= '<td class="sct-day'.esc_attr($day);
			$calendar_output .= ' '.$day_class.'"  title="'.$title_text.'"  onclick="'.$day_onclick.'">'. esc_html($day) . '</td>';

		}

		if ( 6 == sct_calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins) ){
			$newrow = true;
			if($day<$daysinmonth+1){
				$calendar_output .= $endrowicon;
				$endrowicon='';
			}
		}



	}

	$pad = 7 - sct_calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day-1, $thisyear))-$week_begins);
	if ( $pad != 0 && $pad != 7 ){
		$calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr($pad) .'">&nbsp;</td>';
		$calendar_output .= $endrowicon;
		$endrowicon='';

	}
	$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table></div>";


	$calendar_output .= '
			<h2 class="rounded">Calendar Legend</h2>			<div class="textwidget"><ul id="progress-legend">
			<li class="legend-green"><div class="block"></div><div>Optimal Wellness<br>5-7 Rituals completed.</div></li>
			<li class="legend-orange"><div class="block"></div><div>Stable Health<br>3-4 Rituals completed.</div></li>
			<li class="legend-red"><div class="block"></div><div>Out Of Balance<br>1-2 Rituals completed.</div></li>
			<li class="legend-gray"><div class="block"></div><div>Danger - Addictive Behaviour was present.</div></li>
			<li class="legend-litegray"><div class="block"></div><div>Nothing Tracked This Day</div></li>
			</ul>';






	$cache[ $key ] = $calendar_output;
	wp_cache_set( 'sct_get_calendar', $cache, 'calendar' );

	if ( $echo )
		echo apply_filters( 'sct_get_calendar',  $calendar_output );
	else
		return apply_filters( 'sct_get_calendar',  $calendar_output );

}

/**
 * Purge the cached results of get_calendar.
 *
 * @see get_calendar
 * @since 2.1.0
 */
function sct_delete_get_calendar_cache() {
	wp_cache_delete( 'sct_get_calendar', 'calendar' );
}
add_action( 'save_post', 'sct_delete_get_calendar_cache' );
add_action( 'delete_post', 'sct_delete_get_calendar_cache' );
add_action( 'update_option_start_of_week', 'sct_delete_get_calendar_cache' );
add_action( 'update_option_gmt_offset', 'sct_delete_get_calendar_cache' );


?>