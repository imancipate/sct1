<?php
/*Template Include: SelfCare Tracker - Page Header*/
global $current_user, $displayed_user_ID;
//protect from non-admins viewing other person's dataif(@$_REQUEST['user_id']){		if($current_user->data->ID != @$_REQUEST['user_id'] && !current_user_can('administrator'))		die(__('You do not have permission to view this data','selfcare'));	$user_id = @$_REQUEST['user_id'];	$displayed_user_ID = $user_id;}?>