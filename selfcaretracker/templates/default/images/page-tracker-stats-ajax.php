<?php

// create a custom query based on the dates provided
// 

// set displayed user
global $wpdb, $wp_query, $current_user, $displayed_user_ID;


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
$paged = $_GET['paged'];		
$posts_per_page = $_GET['posts_per_page'];		

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

echo '<pre>'.print_r($wp_query->request, true).'</pre>';


$temp_posts = $posts;
$posts = null;
$posts = $wp_query->posts;		
		
//echo '<pre>'.print_r($posts, true).'</pre>';
			
			//post_parent 

$output_array = createDateRangeArray('','');
//cycle through posts and prepare output
foreach($posts as $post){

	$the_date = substr($post->post_date,0,10);
	

	if(stristr($post->post_title,'abstinence')){	
		$output_array[$the_date]['abstinence'] = $post; 
	} else {
		$output_array[$the_date][$post->menu_order] = $post; 
		if($post->post_excerpt=='yes')
			$output_array[$the_date]['yes_count'] = intval($output_array[$the_date]['yes_count'])+1;
		
	}
}

//echo '<pre>'.print_r($output_array, true).'</pre>';exit;

foreach($output_array as $date=>$posts){
	$abstained = '';
	$ritual_output .= '<ul class="success-completed-list">';
	if(is_array($posts)){
		foreach($posts as $key=>$post){

			echo '<pre>'.$key.print_r($post, true).'</pre>';exit;
			
			if($key == 'abstinence'){	
				//output addictive behaviour status
				$header_output .= '<div class="behavior-status">';
				$header_output .= '<strong class="gray6">Addictive behavior was:</strong>';
				if($post->excerpt=='yes'){
					$header_output .= '<span class="behavior-present">Present</span>';
					$abstained = 'no';
				} else {
					$header_output .= '<span class="behavior-notpresent">Not Present</span>';
					$abstained = 'yes';
				}
				$header_output .= '</div>';

			} else {
				//output rituals tracked
				
				if($post->post_excerpt=='yes'){
					$ritual_class='success';
				}else{
					$ritual_class='no_success';
				}
				
				$ritual_output .= '<li class="'.$ritual_class.'">';
				$ritual_output .= $post->post_title; 
				$ritual_output .= '</li >';
			
			}
		}
		$ritual_output .= '</ul>';
	} // if is_array($posts)
	$date_output = '<div class="progress-date-header"><h3>'.$date.'</h3>';
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
	
	
	$row_output = $date_output.$header_output.$ritual_output;
	$total_output .= $row_output;
	$date_output=$header_output=$ritual_output=$row_output = '';
}
echo $total_output;


 
		
?>			
			

			
			
			
			<ul id="progress-list" class="progress-list" data-bind="foreach: myStatus">
				<li class="progress-entry">
					<div class="progress-date-header">
						<h3 data-bind="text: humanDate"></h3>
						<a class="progressicon" data-bind="attr: {'href': editLink}, css: {progressyes: abstained() == 'yes', progressno: abstained() == 'no', progressempty: abstained() == ''}"></a>
					</div>
					<div class="behavior-status" data-bind="visible: abstained() != ''"><strong class="gray6"><?php _e('Addictive behavior was:', 'selfcare'); ?></strong> <span class="behavior-present" data-bind="visible: abstained() == 'no'"><?php _e('Present', 'selfcare'); ?></span><span class="behavior-notpresent" data-bind="visible: abstained() == 'yes'"><?php _e('Not Present', 'selfcare'); ?></span></div>
					<div class="behavior-status" data-bind="visible: abstained() == ''"><?php _e('You have not tracked this day,', 'selfcare'); ?> <a href="#" data-bind="attr: {'href': editLink}"><?php _e('click here to track now', 'selfcare'); ?></a>.</div>
					<div class="success-completed" data-bind="css: {bgred: successCount() <= 2, bgorange: (successCount() > 2 && successCount() < 4), bggreen: successCount() > 4}, visible: successCount() > 0"><span data-bind="text: successCount"></span> <?php _e('Rituals Completed', 'selfcare'); ?></div>
						<ul class="success-completed-list" data-bind="foreach: rituals">
							<li data-bind="text: title"></li>
						</ul>	
					<div class="my-progress-comments" data-bind="visible: journalEntry() != ''">
						<h3><?php _e('My Journal Entry:', 'selfcare'); ?></h3>
						<div class="my-progress-comments-inner" ><em data-bind="text: journalEntry"></em></div>
					</div>
				</li>
			</ul>