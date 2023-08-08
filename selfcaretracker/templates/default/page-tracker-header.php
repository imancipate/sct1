<?php


global $current_user,$displayed_user_ID; 
global $sct_plugin_path, $sct_plugin_url, $wpdb;

$user_id = $current_user->ID;
$displayed_user_ID = $user_id;
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
	if($user_id != @$_GET['user_id'] && !current_user_can('administrator') && $cstm_user_can_access == 0)
		die(__('You do not have permission to view this data','selfcare'));
	$user_id = @$_GET['user_id'];
	$displayed_user_ID = $user_id;
}
$displayed_user_data = get_userdata($displayed_user_ID);
$user_query = '?user_id='.$displayed_user_ID;

?>


<div id="wrapperouter">
	<div id="wrapper">
		<div id="tracker-title-menu">
			<div id="ie9-gradient">
			<div id="tracker-page-title"><h2><?php the_title(); ?></h2></div>
			<div id="tracker-menu">
				<ul><?php 
					if($user_id)
						$user_query='/?user_id='.$user_id; 
					?><li class="first-item"><a href="<?php echo site_url('/'); ?>tracker<?php echo $user_query; ?>#tracker-title-menu" id="tracker-nav-track" ><?php _e('Track', 'selfcare'); ?></a></li>
					<li><a href="<?php echo site_url('/'); ?>tracker-stats<?php echo $user_query; ?>#tracker-title-menu" id="tracker-nav-progress"><?php _e('Progress', 'selfcare'); ?></a></li>
					<li><a href="<?php echo site_url('/'); ?>tracker-settings<?php echo $user_query; ?>#tracker-title-menu" id="tracker-nav-setup"><?php _e('Setup', 'selfcare'); ?></a></li>
					<li class="last-item"><a href="<?php echo site_url('/'); ?>free-session" id="tracker-nav-help"><?php _e('Help', 'selfcare'); ?></a></li>
				</ul> 
			</div><!-- Close: tracker-menu -->
			</div><!-- Close: ie9-gradient -->
		</div><!-- Close: tracker-title-menu -->
