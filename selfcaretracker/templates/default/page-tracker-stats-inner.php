<?php
/*
Template Include: SelfCare Tracker Stats
*/

?>
<script type="text/javascript">
//User Interface
jQuery(document).ready(function(){
	jQuery('#tracker-nav-progress').addClass('active');

	jQuery('#journal_show').click(function(){
		progress_show_journal_entries();
		return false;
	});
	jQuery('#journal_show_all').click(function(){
		progress_show_all();
		return false;
	});
	jQuery('#journal_show_tracked').click(function(){
		progress_show_tracked();
		return false;
	});
});
function progress_show_journal_entries(){
	jQuery('.stats_view_tab.active').removeClass('active');
	jQuery('#stats_tab_journal').addClass('active');
	jQuery('.behavior-status').hide();
	jQuery('.success-completed').hide();
	jQuery('.success-completed-list').hide();
	jQuery('.journal_entry').show();
	jQuery('.journal_entry button').hide();
	jQuery('.delete_day').hide();
	jQuery('.progress-date-header').hide();
	return false;
}
function progress_show_all(){
	jQuery('.stats_view_tab.active').removeClass('active');
	jQuery('#stats_tab_showall').addClass('active');
	jQuery('.behavior-status').show();
	jQuery('.success-completed').show();
	jQuery('.success-completed-list').show();
	jQuery('.journal_entry').show();
	jQuery('.journal_entry button').show();
	jQuery('.delete_day').show();
	jQuery('.progress-date-header').show();
}
function progress_show_tracked(){
	jQuery('.stats_view_tab.active').removeClass('active');
	jQuery('#stats_tab_tracked').addClass('active');
	jQuery('.behavior-status').show();
	jQuery('.success-completed').show();
	jQuery('.success-completed-list').show();
	jQuery('.journal_entry button').hide();
	jQuery('.journal_entry').hide();
	jQuery('.delete_day').show();
	jQuery('.progress-date-header').show();
}

// ## Other ##
</script>

<div class="column-1">
	<div id="tracker_buttons">

		<a id="print_progess" class="tracker_button print_icon" onclick="window.print()" title="Print current report"></a>
	</div>
	<div id="stats_view_tabs_container">
		<ul id="stats_view_tabs">
			<li class="stats_view_tab active" id="stats_tab_showall"><a href="#" id="journal_show_all" class="stats_view_tab_link"><?php _e('All Data','selfcare');?></a></li>
			<li class="stats_view_tab" id="stats_tab_journal"><a href="#" id="journal_show" class="stats_view_tab_link"><?php _e('Journal Entries','selfcare');?></a></li>
			<li class="stats_view_tab" id="stats_tab_tracked"><a href="#" id="journal_show_tracked" class="stats_view_tab_link"><?php _e('Tracker Stats','selfcare');?></a></li>
		</ul>
	</div>

	<div id="my-progress" class="box">

		<?php if(@$displayed_user_data&&current_user_can('administrator')){ ?>
			<h2 id="viewingas"><?php _e('Viewing as: ', 'selfcare');
			echo $displayed_user_data->user_nicename;
			echo '<a href="'.get_bloginfo('url').'/wp-admin/users.php" target="_blank" id="change_user" title="Change displayed user"class="change_user_icon tracker_button"></a>';
			?>

		<?php } else { ?>

			<h2 id="progress_report_header" style="position:relative;"><?php _e('My Progress Report', 'selfcare'); ?>

		<?php } //endif ?>

		</h2>
		<div id="stats-inner">

			<?php
			include($sct_plugin_path.'library/jsoncontrollers/progressajax.php');
			$tracker_stats = new JSON_API_progressajax_Controller;
			echo $tracker_stats->getprogress();
			?>

		</div>
	</div>
	<div class="action-buttons" style="clear: both;display:none;"><button class="viewmore-button" onclick="window.location='<?php bloginfo('url'); ?>/tracker-stats';return false;"><?php _e('View More', 'selfcare'); ?></button></div>
</div><!-- Close: column-1 -->
<div class="column-2">
	<div id="my-success-days" class="box">
		<h2 class="green-grad"><?php _e('Success Days', 'selfcare'); ?></h2>
		<div style="border-top: 1px solid #727272;">
			<?php output_progress_widget($user_id); ?>
			<!--<table id="success-table">
				<thead><tr><th><?php //_e('This week', 'selfcare'); ?></th><th><?php //_e('This month', 'selfcare'); ?></th><th><?php //_e('This year', 'selfcare'); ?></th></tr></thead>
				<tbody>
					<tr><td data-bind="text: successDaysWeek"></td><td data-bind="text: successDaysMonth"></td><td data-bind="text: successDaysYear"></td></tr>
				</tbody>
			</table>
			-->
		</div>
	</div>
	<div id="my-progress-month" class="box">
		<h2 class="progress-calendarh2">
			<?php _e('My Progress Calendar', 'selfcare'); ?>
			<?php include('include-calendar-popin.php');	?>
		</h2>
		<div id="calendar-sidebar">
			<?php //if (!dynamic_sidebar('Calendar Sidebar')) { _e('No Calendar', 'selfcare'); }; ?>

			<div class="sct-calendar-wrapper" id="sct-calendar-wrapper">
				<?php sct_show_calendar();?>
			</div><!-- Close: sct-calendar-wrapper -->
		</div>
	</div>
</div><!-- Close: column-2 -->
<?php
wp_reset_query();
?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			jQuery("#progress_start_date").val("<?php echo $_GET['startdate']?$_GET['startdate']:date('Y-m-d');?>");
			jQuery("#progress_end_date").val("<?php echo $_GET['enddate']?$_GET['enddate']:date('Y-m-d');?>");
		});
	</script>