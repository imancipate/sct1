<?php
/*
Controller name: trackersubmit
Controller description: Submit tracker data
*/

class JSON_API_trackersubmit_Controller {

	public function disableintro() {
		global $current_user; 
		$user_id = $current_user->ID;

		if($_REQUEST['data'] == 'disableIntro'){
			$meta_key = 'sc_intro_disable';
			$meta_value = 'true';
			update_user_meta( $user_id, $meta_key, $meta_value);
		}
		if($_REQUEST['data'] == 'enableIntro'){
			$meta_key = 'sc_intro_disable';
			$meta_value = 'false';
			update_user_meta( $user_id, $meta_key, $meta_value);
		}
	
	}

	public function submitdata() {
		//echo '<pre>'.print_r(file_get_contents("php://input"),true).'</pre>\n\n';

		global $json_api;


		$input = file_get_contents("php://input");


		if($input){
			$var_array = json_decode($input,true);
		} else {
			//$var_array = json_decode($json_test,true);
			die('No data provided');
		}


			if($var_array):
			global $current_user;
			
			//echo '<pre>'.print_r($current_user,true).'</pre>';exit;
			
			$user_id = $current_user->ID;


			if($var_array['userID']){
				if($user_id != $var_array['userID'] && !current_user_can('administrator'))
					die(__('You do not have permission to view this data','selfcare'));
				$user_id = $var_array['userID'];
				$displayed_user_ID = $user_id;
			}
			$user_data = get_userdata( $user_id );

			$username = $user_data->user_login;
			if(!$username){die('no username');}
			//add the addictions token
			$format = "Y-m-d"; //or something else that date() accepts as a format
			if($var_array['date']){
				$time = date_format(date_create($var_array['date']), $format);
			} else {
				$now = current_time('mysql');
				$time = date_format(date_create($now), $format);
			}



			//check for previous post on this day
			$parent_id = $var_array['retrackID'];
			//echo '<pre>'.print_r($parent_id,true).'</pre>';exit;
			if($parent_id){
				deleteDay($parent_id);
			}
			
			$data = array(
				'post_author' => $user_id,
				'post_title' => $username.' - Abstinence Tracked', 
				'post_excerpt' => $var_array['abstained'],
				'post_content' => $var_array['addiction'],
				'post_type' => 'tracked_ritual',
				'post_date' => $time,
				'post_status' => 'publish',
				'post_format' => 'abstinence'
			);


			//echo '<pre>'.print_r($data,true).'</pre>';
			//exit;
			
			$newabstinenceid = wp_insert_post($data);
			//escape the data

				$input =	str_replace('\n','<br />',$input);
				//print_r(	$input );
			update_post_meta($newabstinenceid, 'sct_jsondata', $input);

				//echo $newabstinenceid; exit;
					
			
		// now add the rituals as comments related to the parent post (ritual)	
			$count = 1;
			foreach($var_array['rituals'] as $ritual){
				if($ritual['id']!=""){

					$post = array(
						'post_parent' => $newabstinenceid,
						'post_author' => $user_id,
						'post_excerpt' => $ritual['done'],
						'post_title' => $ritual['title'],
						'post_content' => $ritual['description'],
						'post_type' => 'tracked_ritual',
						'post_date' => $time,
						'post_status' => 'publish',
						'pinged' => $ritual['id'],
						'menu_order' => $count,
					);

					
				//echo '<pre>'.print_r($post,true).'</pre>';
				$newpostid = wp_insert_post($post);
				//echo $newpostid;
				//exit;
				$count++;
				} // end if id
			
			} //end foreach

			
		// now we add the journal entry
			$journal_entry = $var_array['journalEntry'];
			if($journal_entry){
				$post = array(
					'ID' => '',
					'post_parent' => $newabstinenceid,
					'post_excerpt' => $journal_entry,
					'post_content' => $var_array['abstained'],
					'post_author' => $user_id,
					'post_status' => 'publish',
					'post_title' => $username.' - Journal Entry', 
					'post_type' => 'journal_entry', 
					'post_date' => $time,
				);
				
				//echo '<pre>'.print_r($post,true).'</pre>';
				
				$newpostid = wp_insert_post($post);
				//echo $newpostid; 
				
				return $newpostid;
			}
			
		endif;																
	}
	
	public function submitsetup() {
	
			
		$input = file_get_contents("php://input");
		$var_array = json_decode($input,true);

		 
			if($var_array):
				global $current_user;
				$user_id = $current_user->ID;

				//echo '<pre>'.print_r($var_array,true).'<pre>';exit;
				if($var_array['userID']){
					if($user_id != $var_array['userID'] && !current_user_can('administrator'))
						die(__('You do not have permission to view this data','selfcare'));
					$user_id = $var_array['userID'];
					$displayed_user_ID = $user_id;
					$displayed_user_data = get_userdata($displayed_user_ID);
				}

				//check for setup date for active user - if none set, then set one
				$sc_setupdate_set = get_user_meta( $user_id, 'sc_setupdate', true);

				if(!$sc_setupdate_set)
					update_user_meta( $user_id, 'sc_setupdate', strtotime(date('Y-m-d')) );

				//echo 'sd:'.$sc_setupdate_set;exit;

					
			// check the postmethod and update accordingly
			switch ($var_array['action']){
				// is this a usermeta change?
				case 'updateMeta':
					// save the commitments and addictions to the usermeta
					
			
					$meta_key = 'sc_commitment';
					$meta_value = $var_array['commitment'];
					$updateCommitment = update_user_meta( $user_id, $meta_key, $meta_value );
					//echo $user_id.' | '.$meta_key.' | '.$meta_value.' <br> ';
					//echo "<br>Updated Commitment: ".$updateCommitment;
					$meta_key = 'sc_addiction';
					$meta_value = $var_array['addiction'];
					$updateAddiction = update_user_meta( $user_id, $meta_key, $meta_value );
				
					if($updateCommitment){echo "Commitment updated:\n".$var_array['commitment']."\n";}
					if($updateAddiction){echo "Addiction updated:\n".$var_array['addiction']."\n";}
					
					//echo "<br>Updated Addiciton: ".$updateAddiction;
				
				break;
				//is this an order change?
				case 'updateOrder':
					//create an array of id's and menu order
					//echo "Action: menuorder\n";
					//save menu order for each post
					$counter = 1;
					foreach($var_array['rituals'] as $ritual){
						$post = array(
						  'ID' => $ritual['id'],
						  'menu_order' =>  $counter,
						);
						$newpostid = wp_update_post($post);
						echo 'Updated post: '.$newpostid."\n";
						$counter++;
					}	
					$count = $counter - 1;
					echo $count." items reordered\n";
				break;
				//is this a deleted item?
				case 'deleteRitual':
					//echo "Action: deleteRitual".$var_array['ritualID']."\n";
					if($var_array['ritualID']){
						// move the post into 'deleted' status
								$post = array(
								  'ID' => $var_array['ritualID'],
								  'post_status' => 'draft',
								);
								$delpostid = wp_update_post($post);
								echo 'drafted: '.$delpostid;
					}
				break;
				// is this a change to rituals
				case 'addRitual':
					


				foreach($var_array['rituals'] as $ritual){
						//print_r($ritual);
						//continue;
						//find the empty one
						if($ritual['id']==""){
					
							$term_Array = get_term_by( 'name', $ritual['category'], 'scr_category', ARRAY_A);
							$term_id = $term_Array['term_id'];
							$term_name = $term_Array['name'];
							//echo $term_id;exit;
							$post = array(
							  'post_excerpt' => strip_tags($ritual['description']),
							  'post_content' => strip_tags($ritual['description']),
							  'post_author' => $user_id,
							  'post_status' => 'publish',
							  'post_title' => $ritual['title'], 
							  'post_type' => 'scr_custom', 
							  'tax_input' => array( 'scr_category' => array( $term_name ) ) 
							);
							$newpostid = wp_insert_post($post, true);
			
							wp_set_post_terms( $newpostid , $term_name , 'scr_category' );
							
							 
							
							//echo "\naddRitual: ".$newpostid.print_r($post, true);
							echo $newpostid;
							exit;
						}
					
					} //end foreach
				break;
				case 'updateRitual':
					echo "updateRitual:".$var_array['ritualID'];
					// check which ritual has been added / edited by edit flag 
					if($var_array['ritualID']){
						// move the post into 'deleted' status
						foreach($var_array['rituals'] as $ritual){
							if($ritual['id']==$var_array['ritualID']){
								// this is the one, boss
								
							
								$post = array(
								  'ID' => $ritual['id'],
								  'post_excerpt' => strip_tags($ritual['description']),
								  'post_content' => strip_tags($ritual['description']),
								  'post_title' => $ritual['title'], 
								  'post_type' => 'scr_custom'
								);
								 
								$newpostid = wp_update_post($post);
							
								$term_Array = get_term_by( 'name', $ritual['category'], 'scr_category', ARRAY_A);
								$term_id = $term_Array['term_id'];
								$term_name = $term_Array['name'];
								wp_set_post_terms( $newpostid , $term_id , 'scr_category' );
								
								echo "updated:".$newpostid;
								print_r($post);
							}	
						}
					}
				break;
				default:
				break;
			} // end select
		else:
			//$var_array = json_decode($json_test,true);
			die('No data provided');

		endif;
	
	
	
	
	
	}
  
}

?>
