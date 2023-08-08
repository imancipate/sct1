<?php

	$calendar_set_dates = '<a id="set_dates" class="calendar_button calendar_icon" onclick="jQuery(\'#set_dates_container\').toggle();" title="Set custom progress report dates"></a>';
	echo $calendar_set_dates;
	$calendar_popin = '
				<div id="set_dates_container">
					<h2>Custom Progress Report</h2>
					<label>Start Date: <input placeholder="YYYY-MM-DD" type="text" id="progress_start_date" class="datepicker" value="" /></label><br />
					<label>End Date: <input placeholder="YYYY-MM-DD" type="text" id="progress_end_date" class="datepicker" value="" /></label><div style="height:15px;clear:both;" />
					<button id="set_dates_go" style="float:right;" onclick="get_stats(jQuery(\'#progress_start_date\').val(),jQuery(\'#progress_end_date\').val(), \''.$user_id.'\');jQuery(\'#set_dates_container\').hide();return false;">Go</button>
					<button id="set_dates_cancel" style="float:left;" onclick="jQuery(\'#progress_start_date\').val(\'\');jQuery(\'#progress_end_date\').val(\'\');jQuery(\'#set_dates_container\').hide();return false;">Cancel</button>
				</div>';
echo $calendar_popin;
?>