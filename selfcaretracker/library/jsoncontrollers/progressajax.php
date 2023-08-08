<?php
/*
Controller name: progressajax
Controller description: Get progress stats
*/

class JSON_API_progressajax_Controller {

  public function getprogress() {

//display messages	
	if(@$_GET['message']){
		switch(@$_GET['message']){
			case 'tracked':
			
				$messagetext = __('You have already tracked today.  Select a date in the calendar to track a different day','selfcare');
				
			break;
			default:
			break;
		}
		$output_message = '<div class="messagetext">'.$messagetext.'</div>';
	}

	global $wpdb, $wp_query, $current_user, $displayed_user_ID, $output_array;
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
	if(@$_REQUEST['user_id']){
		if($current_user->data->ID != @$_REQUEST['user_id'] && !current_user_can('administrator') && $cstm_user_can_access == 0)
			die(__('You do not have permission to view this data','selfcare'));
		
		$user_id = @$_REQUEST['user_id'];
		$displayed_user_ID = $user_id;
	}


	//echo '<pre>'.print_r($current_user, true).'</pre>';

	$query = array(
		'post_type' =>	'tracked_ritual',
		'paged' => 0,
		'posts_per_page' => 1,
		'post_status' =>	'publish',
		'post_author' => 	$displayed_user_ID,
		'nopaging' => true,
		'orderby' => 'date',
		'order' => 'DESC',
	);


	//override by url
	$paged = @$_REQUEST['paged'];		
	$posts_per_page = @$_REQUEST['posts_per_page'];		

	if ($paged) {
		$query['paged'] = $paged;
		$query['nopaging'] = false;
	}

	if ($posts_per_page) {
		$query['posts_per_page'] = $posts_per_page;
		$query['nopaging'] = false;

	}

	$temp = $wp_query;
	$wp_query = null;
	add_filter( 'posts_where', 'filter_progress_where' );
	$wp_query = new WP_Query();
	$wp_query->query($query);
	remove_filter( 'posts_where', 'filter_progress_where' );

	$posts = $wp_query->posts;

    //$output_array = createDateRangeArray('','');
    $output_array = array();
	//cycle through posts and prepare output
	foreach($posts as $post){

		$the_date = substr($post->post_date,0,10);
		

		if(stristr($post->post_title,'abstinence')){	
			$output_array[$the_date]['abstinence'] = $post->to_array(); 
		} elseif($post->post_type=='journal_entry'){	
			$output_array[$the_date]['journal'] = $post->to_array(); 
		} else {
			$output_array[$the_date][$post->menu_order] = $post->to_array(); 
			if($post->post_excerpt=='yes')
				@$output_array[$the_date]['yes_count'] = intval($output_array[$the_date]['yes_count'])+1;
			if($post->post_excerpt=='no')
				@$output_array[$the_date]['no_count'] = intval($output_array[$the_date]['no_count'])+1;
			
		}
	}

	//echo '<pre>'.print_r($output_array, true).'</pre>';exit;
	foreach($output_array as $date=>$posts){


				//check if future date and hide
				$currentTimestamp = current_time('timestamp');

				if(strtotime($date)>$currentTimestamp){
					$future_date = true;
					continue;
				} else {
					$future_date = false;
				}

				//check setupdate and disable any previous days
				$meta_key = 'sc_setupdate';
				$setupDate = get_user_meta( $user_id, $meta_key, true );
				//echo 'setup: '.date('Y m d',$setupDate)."<br>";
				//echo 'this: '.date('Y m d',strtotime($date))."<br>";

				if(strtotime($date)<$setupDate-86400){ //before setup date
					$past_date = true;
					continue;
				} else {
					$past_date = false;
				}
						



			$abstained = '';

			
			if(is_array($posts)){
				ksort($posts);
					
				//echo '<pre>'.$key.print_r($posts, true).'</pre>';
					
				$ritual_count = 0;
				$ritual_output = '<ul class="success-completed-list" style="display:block;">';
				//$posts_array = ksort($posts);
				//var_dump($posts);
				foreach($posts as $key=>$post){

					
					if($key == 'journal'){	
						//$journal_output = '<div class="journal_entry" onclick="$(\'#journal_'.$post->ID.'\').toggle();return false;"><button>'.__('Journal Entry','selfcare').' - '.$date.' - '.__('Click to View','selfcare').'</button>';
						$journal_output = '<div class="journal_entry" style="display:block;">'.date('D. M. d, Y',strtotime($date)).' - '.__('Journal Entry','selfcare').'<div class="journal_entry_text" id="journal_'.$post['ID'].'" style="display:block;">';
						//$journal_output .= $post->post_parent.' - ';
						$journal_text = nl2br($post['post_excerpt']);
						$journal_output .= $journal_text;
						$journal_output .= '</div></div>';
					
					} elseif($key == 'abstinence'){	
						//output addictive behaviour status
						$main_parent_id = $post['ID'];
						$header_output = '<div class="behavior-status" style="display:block;">';
						//$main_parent_id;
						$header_output .= '<strong class="gray6">'.__('Behaviour that threatens my self-care was:','selfcare').'</strong>';
						
						//echo '<pre>'.print_r($post, true).'</pre>';
						if($post['post_excerpt']=='no'){
							$header_output .= '<span class="behavior-present">Present</span>';
							$abstained = 'no';
						} else {
							$header_output .= '<span class="behavior-notpresent">Not Present</span>';
							$abstained = 'yes';
						}
						$header_output .= '</div>';

					} elseif($key == 'yes_count'){
					
						switch($post){
							case 0:
							case 1:
							case 2:
								$count_css = 'bgred';
							break;
							case 3:
							case 4:
								$count_css = 'bgorange';
							break;
							case 5:
							case 6:
							case 7:
								$count_css = 'bggreen';
							break;
							default:
								$count_css = 'bglitegray';
							break;
						} // end switch
						$count_total = $post;
						$no_count = '';
					} elseif($key == 'no_count'){
							$no_count = true;
					} else {
						//output rituals tracked
						$ritual_count++;
						
						if($post['post_excerpt']=='yes'){
							$ritual_class='success';
						}else{
							$ritual_class='no_success';
						}
						


						$ritual_output .= '<li class="'.$ritual_class.'">'; 
						//$ritual_output .= $post->post_parent.' - ';
						$ritual_output .= $post['post_title']; 
						$ritual_output .= '</li >';
						$no_count = '';
					}
				}
				$ritual_output .= '</ul>';
				$no_rows = '';
			} else { $no_rows = true; }// if is_array($posts)
			
			
			if(!$no_rows){
				if($no_count && !$count_css){
					$count_css = 'bggray';
					$count_total='0';
				}
					$count_output = '<div class="success-completed '.$count_css.'" style="display:block;"><span>'.$count_total.'</span> '.__(' Self-Care Rituals Successfully Completed','selfcare').'</div>';
				
			}
			

			
			$format_date = date('l F d, Y',strtotime ($date));

			$date_output = '<div class="progress-date-header" style="display:block;"><h3>'.$format_date.'</h3>';
			
			if($no_rows){
				if($displayed_user_ID){
					$user_query = '&user_id='.$displayed_user_ID;
				}
				$track_link = get_bloginfo('url').'/tracker/?date='.$date.$user_query;
				$date_output .= '<a class="track_link" href="'.$track_link.'" title="'.__('You haven\'t tracked this day, click here to track now!','selfcare').'"></a>'; 
			} else {
			
				
				//only show the edit link for compatible posts
				$retrack_jsondata = get_post_meta( $main_parent_id,'sct_jsondata', 'true' );
				if($retrack_jsondata){
					$track_link = '';
					$user_link = '&user_id='.$displayed_user_ID;
					$retrack='&retrack=true&message=retrack';
					$location = get_bloginfo('url').'/tracker/?date='.esc_sql($date).$user_link.$retrack;
					//$location = get_bloginfo('url').'/tracker/?date='.mysqli_real_escape_string($date).$user_link.$retrack;
					$date_output .= '<a id="retrack_day" href="'.$location.'");return false;" title="'.__('Edit this Day\'s Entry','selfcare').'"></a>';
				}				
				
				// DELETE RETRACK LINK
				$date_output .= '<a id="delete_day" href="#" onclick="deleteDay(\''.$main_parent_id.'\',\''.$displayed_user_ID.'\',\''.$date.'\');return false;" title="'.__('Delete this day\'s data and re-track','selfcare').'"></a>';

			}
			

			$date_output .= '<a class="progressicon" class="';
			
			switch($abstained){
				case 'yes':
					$date_output .= 'progressyes';
				break;
				case 'no':
					$date_output .= 'progressno';
				break;
				default:
					$date_output .= 'progressempty';
				break;	
			} //end switch 
			
			$date_output .= '"></a>';
			$date_output .= '</div>';
			
			//if(!$no_rows)
				//$delete_output = '<button class="delete_day" style="   margin: 10px 20px 20px 136px;padding: 15px;" onclick="deleteDay(\''.$main_parent_id.'\',\''.$displayed_user_ID.'\',\''.$date.'\');return false;">'.__('Delete this day\'s data and re-track','selfcare').'</button>';
				
			@$row_output = $date_output.$header_output.$count_output.$ritual_output.$journal_output.$delete_output;
			@$total_output .= $row_output;
			@$date_output=$no_count=$top_output=$count_output=$header_output=$ritual_output=$journal_output=$row_output=$no_rows=$delete_output = '';
		
		
			
			//return 'row_output '.$row_output;exit;
			// if only one and not tracked, route to tracker for this date

			//moved this to calendar

			//if(count($output_array)==1&&$track_link){
				//echo 'one day no rows';exit;
				//return '<script type="text/javascript">window.location="'.$track_link.'";</script>';
			//}
			
			//populate the search dates
			$total_output .= '<script type="text/javascript">';
			$total_output .= 'jQuery( "#progress_start_date" ).val("'.@$_REQUEST['startdate'].'");';
			$total_output .= 'jQuery( "#progress_end_date" ).val("'.@$_REQUEST['enddate'].'");';
			$total_output .= '</script>';
			

		
		} //end freach output array as posts
		
		
		if(@$_REQUEST['startdate']&&(@$_REQUEST['startdate']!=@$_REQUEST['enddate'])){
			$top_output = '<div class="progress-report-date-header" onclick="jQuery(\'#set_dates_container\').toggle();">From: '.$_REQUEST['startdate'].' to: '.$_REQUEST['enddate'].'</div>';
		} 
		

		
		return @$output_message.$top_output.$total_output;
			
	}
}

?>
