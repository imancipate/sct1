<?php


function filter_progress_where( $where = '' ) {
	// set displayed user
	global $wpdb, $wp_query, $current_user, $displayed_user_ID;
	
	
	if(!$displayed_user_ID){ 
		$displayed_user_ID = $current_user->ID; 
	}
	else
	{
		//check permissions
		if($displayed_user_ID != $current_user->ID && !current_user_can('administrator')){
			//no permission
			die('You do not have permission to view this data');
		}
	}

	$currentdate = date('Y-m-d',current_time('timestamp',1));
	$startdate = strtotime ( '-7 day' ) ;
	$startdate = date ( 'Y-m-d' , $startdate );

	if($_GET['startdate']){
		$strDateFrom = $_GET['startdate'];		
	} else {
		$strDateFrom =  $startdate; 
	}

	if($_GET['enddate']){
		$strDateTo = $_GET['enddate'];		
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
	
	

	echo "where: ".$where;
	


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
	
	if($_GET['startdate']){
		$strDateFrom = $_GET['startdate'];		
	} else {
		$strDateFrom =  $startdate; 
	}

	if($_GET['enddate']){
		$strDateTo = $_GET['enddate'];		
	} else {
		$strDateTo =  $currentdate;
	}
	
	
    $aryRange=array();

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



?>