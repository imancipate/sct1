<?php
/*
Template Include: SelfCare Tracker
*/


$defaultJournalEntry = json_encode(__('How did today go? Enter the \'wins\' you are celebrating from today or the challenges you faced that you can handle better tomorrow. (Optional)', 'selfcare'));


// get the passed date
$trackDate = $_REQUEST['date'];

//get the tracked data
$abstinence_tracked = abstinence_tracked($trackDate, $user_id);
if($abstinence_tracked){
	$parent_id = $abstinence_tracked[0]['ID'];
	$retrack_jsondata = get_post_meta( $parent_id,'sct_jsondata', 'true' );
	//echo '<pre>'.print_r($retrack_jsondata,true).'</pre>';exit;
}


?>


<script type="text/javascript" id="tracker_inline_js_1">




var weekday=new Array(7);
weekday[0]="Sunday";
weekday[1]="Monday";
weekday[2]="Tuesday";
weekday[3]="Wednesday";
weekday[4]="Thursday";
weekday[5]="Friday";
weekday[6]="Saturday";
var month=new Array(12);
month[0]="January";
month[1]="February";
month[2]="March";
month[3]="April";
month[4]="May";
month[5]="June";
month[6]="July";
month[7]="August";
month[8]="September";
month[9]="October";
month[10]="November";
month[11]="December";






//User Interface
jQuery(document).ready(function(){
	jQuery('#tracker-nav-track').addClass('active');
	jQuery('#journal-entry').elastic();
	//jQuery('.finish-button').each(function() { jQuery(this).click(function() { window.location = './tracker-stats';}); });
});
// ## View Model ##
function Ritual(data) {
	var data = data || {};
	this.id = ko.observable(data.id || '');
	this.category = ko.observable(data.category || '');
	this.title = ko.observable(data.title || '');
	this.description = ko.observable(data.description || '');
	this.done = ko.observable(data.done || '');
	this.date = ko.observable(data.date || new Date());
}
//console.debug('yo1');
var mySelfCare = {
	date: ko.observable(new Date()),
	userID: ko.observable(''),
	niceDate: ko.observable(''),
	urlDate: ko.observable(''),
	addiction: ko.observable(''),
	commitment:  ko.observable(''),
	abstained: ko.observable(''),
	journalEntry:  ko.observable(<?php echo $defaultJournalEntry; ?>),
	rituals: ko.observableArray([]),
	selectedRitual: ko.observable(new Ritual()),
	categories: ko.observableArray([]),
	successDaysWeek: ko.observable(''),
	successDaysMonth: ko.observable(''),
	successDaysYear: ko.observable(''),
	retrackID: ko.observable('')
};
//console.debug('yo2');
jQuery(document).ready(function(){ ko.applyBindings(mySelfCare); });
// ## Actions and Click Handlers ##
mySelfCare.abstainedYes = function() { mySelfCare.abstained('yes'); };
mySelfCare.abstainedNo = function() { mySelfCare.abstained('no'); };
mySelfCare.doYes = function() { this.done('yes'); };
mySelfCare.doNo = function() { this.done('no'); };
// ## Data Loading and Saving ##
mySelfCare.saveData = function() {
	var isValidated = true;
	var ritualsValid = true;
	if (!jQuery('#my-sobriety-today').hasClass('yes') && !jQuery('#my-sobriety-today').hasClass('no')) { isValidated = false; alert('Please enter your sobriety for the day.'); event.preventDefault();};
	jQuery('li.my-ritual-row').each(function() { if (!jQuery(this).hasClass('yes') && !jQuery(this).hasClass('no')) { ritualsValid = false; }; });
	if (isValidated && !ritualsValid) { isValidated = false; alert('Please select Yes or No for each of your rituals for the day.');event.preventDefault(); };
	if (isValidated) {
		jQuery('#finish-button').attr('disabled', true); jQuery('#finish-button').fadeTo(300,'0.2');jQuery('#sct-submit-ajax-spinner').show();
		// Remove Default Values
		if (mySelfCare.journalEntry() == <?php echo $defaultJournalEntry; ?>) { mySelfCare.journalEntry(''); };
		// console.log(ko.toJSON(mySelfCare));
		<?php if($displayed_user_ID){
			$user_query = '&user_id='.$displayed_user_ID;
		}?>

		//console.log(mySelfCare);

		var mySave = jQuery.post("<?php echo get_bloginfo('url');?>/api/trackersubmit/submitdata/", ko.toJSON(mySelfCare)).done( function(data) {

			window.location = '../tracker-stats?startdate=' + mySelfCare.urlDate() + '&enddate=' + mySelfCare.urlDate() + '<?php echo $user_query;?>#tracker-title-menu';
			//console.log(data);
		});
		// Reset Default Values
		if (mySelfCare.journalEntry() == '') { mySelfCare.journalEntry(<?php echo $defaultJournalEntry; ?>); };
	};
};


var doLoadData = true;
//var doLoadData = false;

mySelfCare.loadData = function() {

	//console.debug('yo4');

	userID = '<?php echo $user_id;?>';
	var dataDate = '<?php echo $trackDate; ?>';
	if (dataDate != '') {
		newDate = new Date('<?php echo $trackDate; ?>T00:00:00Z');
		urlDate = '<?php echo $trackDate; ?>';
		dataDate = '<?php echo $trackDate; ?>T00:00:00Z';
		niceDate = '<?php echo date('l, F d, Y',strtotime ($trackDate));?>';
	} else {
		newDate = new Date();
		var dateDay = newDate.getDate();
		var dateMonth = newDate.getMonth()+1;
		var dateYear = newDate.getFullYear();
		urlDate = dateYear + '-' + dateMonth + '-' + dateDay;
		dataDate = urlDate + 'T00:00:00Z';
		niceDate = weekday[newDate.getDay()] + ', ' + month[dateMonth-1] + ' ' + dateDay + ', ' +dateYear;
		//alert('new(js):'+newDate);alert('urlDate:'+urlDate);alert('date:'+dataDate);alert('nice'+niceDate);
	};
	var dataAbstained = '';
	var dataJournalEntry = '';
	var successDaysWeek = '';
	var successDaysMonth = '';
	var successDaysYear = '';
	var retrackID = '';
	mySelfCare.date(dataDate);
	mySelfCare.userID(<?php echo $user_id; ?>);
	mySelfCare.niceDate(niceDate);
	mySelfCare.urlDate(urlDate);
	mySelfCare.addiction(<?php echo json_encode(get_user_meta($user_id, 'sc_addiction', true));  ?>);
	mySelfCare.commitment(<?php echo json_encode(get_user_meta($user_id, 'sc_commitment', true));  ?>);
	mySelfCare.abstained(dataAbstained);
	mySelfCare.journalEntry(dataJournalEntry);
	mySelfCare.successDaysWeek(successDaysWeek);
	mySelfCare.successDaysMonth(successDaysMonth);
	mySelfCare.successDaysYear(successDaysYear);
	mySelfCare.retrackID(retrackID);
	//console.log(ko.toJSON(mySelfCare));

	jQuery.getJSON('<?php echo get_bloginfo('url'); ?>/api/scrcustom/get_author_posts/?author_id=<?php echo $user_id; ?>&post_type=scr_custom&orderby=menu_order&order=ASC&dev=1', function(data) {
		mySelfCare.rituals([]);
		jQuery.each(data.posts, function() {
			var tmpRitual = {};
			tmpRitual.id = this.id;
			tmpRitual.category = this.scr_category[0];
			tmpRitual.title = this.title;
			tmpRitual.description = this.excerpt;
			mySelfCare.rituals.push(new Ritual(tmpRitual));
		});
console.log(data);
		if (mySelfCare.addiction() == '' || mySelfCare.commitment() == '' || mySelfCare.rituals().length < 5) { alert('<?php _e('You need to finish setup for your Tracker, first.', 'selfcare'); ?>'); window.location = '<?php echo get_bloginfo('url'); ?>/tracker-settings/?user_id='+mySelfCare.userID(); }
	});


	jQuery('#journal-entry').elastic();
};

mySelfCare.loadParsedData = function(parsed) {
	//console.log('ko parsed:');
	//console.log(parsed);
	userID = '<?php echo $user_id;?>';
	var dataDate = '<?php echo $trackDate; ?>';
	if (dataDate != '') {
		newDate = new Date('<?php echo $trackDate; ?>T00:00:00Z');
		urlDate = '<?php echo $trackDate; ?>';
		dataDate = '<?php echo $trackDate; ?>T00:00:00Z';
		niceDate = '<?php echo date('l, F d, Y',strtotime ($trackDate));?>';
	} else {
		newDate = new Date();
		var dateDay = newDate.getDate();
		var dateMonth = newDate.getMonth()+1;
		var dateYear = newDate.getFullYear();
		urlDate = dateYear + '-' + dateMonth + '-' + dateDay;
		dataDate = urlDate + 'T00:00:00Z';
		niceDate = weekday[newDate.getDay()] + ', ' + month[dateMonth-1] + ' ' + dateDay + ', ' +dateYear;
		//alert('new(js):'+newDate);alert('urlDate:'+urlDate);alert('date:'+dataDate);alert('nice'+niceDate);
	};
	var dataAbstained = parsed.abstained;
	var dataJournalEntry = parsed.journalEntry;
	var successDaysWeek = '';
	var successDaysMonth = '';
	var successDaysYear = '';
	var retrackID = '<?php echo $parent_id; ?>';
	mySelfCare.date(dataDate);
	mySelfCare.userID(<?php echo $user_id; ?>);
	mySelfCare.niceDate(niceDate);
	mySelfCare.urlDate(urlDate);
	mySelfCare.addiction(<?php echo json_encode(get_user_meta($user_id, 'sc_addiction', true));  ?>);
	mySelfCare.commitment(<?php echo json_encode(get_user_meta($user_id, 'sc_commitment', true));  ?>);
	mySelfCare.abstained(dataAbstained);
	//console.log('valueshouldbe: ');
	//console.log(mySelfCare.abstained());
	mySelfCare.journalEntry(dataJournalEntry);
	mySelfCare.successDaysWeek(successDaysWeek);
	mySelfCare.successDaysMonth(successDaysMonth);
	mySelfCare.successDaysYear(successDaysYear);
	mySelfCare.retrackID(retrackID);
	mySelfCare.rituals([]);
	jQuery.each(parsed.rituals, function() {
		var tmpRitual = {};
		tmpRitual.id = this.id;
		tmpRitual.date = this.date;
		tmpRitual.done = this.done;
		tmpRitual.category = this.category;
		//tmpRitual.category = this.scr_category[0];
		tmpRitual.title = this.title;
		tmpRitual.description = this.description;
		mySelfCare.rituals.push(new Ritual(tmpRitual));
	});

	//console.log('msc rituals:');
	//console.log(mySelfCare.rituals());

	jQuery('#journal-entry').elastic();
};

/*

 // Load Dummy Data
 var doLoadDummyData = true;
 function loadDummyData() {
 var dummyDateData = ''+Date();
 var dummySuccessDaysWeek = '4';
 var dummySuccessDaysMonth = '9';
 var dummySuccessDaysYear = '36';
 mySelfCare.successDaysWeek(dummySuccessDaysWeek);
 mySelfCare.successDaysMonth(dummySuccessDaysMonth);
 mySelfCare.successDaysYear(dummySuccessDaysYear);
 };


 jQuery(document).ready(function(){ if (doLoadDummyData == true) { loadDummyData(); }; });
 */



// ## Other ##

</script>
<div class="column-1">
	<?php

	//display messages
	if($_GET['message']){
		switch($_GET['message']){

			case 'retrack':

				$messagetext = __('You are now editing your existing entry for this day','selfcare');
				$messagetext .= ' - <a href="'.get_bloginfo('url').'/tracker-stats/?user_id='.$user_id.'" id="tracker_edit_cancel" >Cancel Edit</a>';
				// pull the data for this day

				break;
			default:
				break;
		}
		$output_message = '<div class="messagetext">'.$messagetext.'</div>';
	}


	?>
	<?php echo $output_message; ?>
	<div id="my-date" class="box" data-bind="visible: niceDate">
		<h2 id="trackingdate"><?php _e('Tracking for: ', 'selfcare'); ?><span data-bind="text: niceDate"></span></h2>
		<?php if($displayed_user_data&&current_user_can('administrator')){ ?>
			<h2 id="viewingas"><?php _e('Tracking as: ', 'selfcare');
				echo $displayed_user_data->user_nicename;
				echo '<a href="'.get_bloginfo('url').'/wp-admin/users.php" target="_blank" id="change_user" title="Change displayed user"class="change_user_icon tracker_button"></a>';?>
			</h2>
		<?php } //endif ?>
	</div>
	<div id="my-commitment-usage" class="box">
		<h2><?php _e('Why I\'m Committed To My Self-Care Today', 'selfcare'); ?></h2>
		<p data-bind="text: commitment"></p>
	</div>
	<div id="my-sobriety-today" class="box" data-bind="css: {yes: abstained() == 'yes', no: abstained() == 'no'}">
		<h2><?php _e('My Sobriety', 'selfcare'); ?></h2>
		<div class="my-ritual-row">
			<h3 class="ritual-title"><?php _e('I was successful in abstaining from behaviors that threaten my self-care today', 'selfcare'); ?></h3>
			<div class="ritual-yes-no">
				<a class="yesno button-yes" data-bind="click: abstainedYes"><?php _e('Yes', 'selfcare'); ?></a>
				<a class="yesno button-no" data-bind="click: abstainedNo"><?php _e('No', 'selfcare'); ?></a>
				<a class="help-icon" title="<?php _e('Click to Show/Hide description', 'selfcare'); ?>" onclick="jQuery(getnextsibling(this.parentNode)).slideToggle('slow'); jQuery(this).toggleClass('help-icon-active');"></a>
			</div>
			<div class="ritual-description" id="sc_addiction"><p data-bind="text: addiction"></p></div>
		</div>
	</div>
	<div id="my-rituals" class="box">
		<h2><?php _e('My Self-Care Rituals', 'selfcare'); ?>

			<a class="help-icon" id="showall-ritual-descriptions" style="float:right" title="<?php _e('Click to Show/Hide all Ritual descriptions', 'selfcare'); ?>" onclick="if(!jQuery(this).hasClass('help-icon-active')){jQuery('#my-rituals .ritual-description').slideDown('slow'); jQuery(this).addClass('help-icon-active');jQuery('#my-rituals .ritual-yes-no .help-icon').addClass('help-icon-active');}else{jQuery('#my-rituals .ritual-description').slideUp('slow'); jQuery(this).removeClass('help-icon-active');jQuery('#my-rituals .ritual-yes-no .help-icon').removeClass('help-icon-active');}"></a>

		</h2>
		<ul id="my-ritual-list" data-bind="foreach: rituals">
			<li class="my-ritual-row" data-bind="css: {yes: done() == 'yes', no: done() == 'no'},attr: { id: 'ritualrow_' + id() }">
				<h3 class="ritual-title" data-bind="text: title"></h3>
				<div class="ritual-yes-no">
					<a class="yesno button-yes" data-bind="click: $root.doYes"><?php _e('Yes', 'selfcare'); ?></a>
					<a class="yesno button-no" data-bind="click: $root.doNo"><?php _e('No', 'selfcare'); ?></a>
					<a class="help-icon" title="<?php _e('Click to Show/Hide description', 'selfcare'); ?>" onclick="jQuery(getnextsibling(this.parentNode)).slideToggle('slow'); jQuery(this).toggleClass('help-icon-active');"></a>
				</div>
				<div class="ritual-description" id="sc_description"><p data-bind="text: description"></p></div>
			</li>
		</ul>
	</div>
	<div id="my-journalentry" class="box">
		<h2><?php _e("Today's Journal Entry (Optional)", 'selfcare'); ?></h2>
		<div>
			<textarea name="journal-entry" id="journal-entry" class="edit-input" placeholder=<?php echo $defaultJournalEntry; ?> data-bind="value: journalEntry"></textarea>
		</div>
	</div>
	<div class="action-buttons" style="clear: both;position:relative;"><button class="previousday-button"><?php _e('Previous Day', 'selfcare'); ?></button>
		<div id="sct-submit-ajax-spinner"></div>
			<button class="nextday-button"><?php _e('Next Day', 'selfcare'); ?></button> <button class="finish-button" id="finish-button" data-bind="click: saveData"><?php _e('Save my progress for the day', 'selfcare'); ?></button></div>
</div><!-- Close: column-1 -->
<div class="column-2">
	<div id="my-success-days" class="box">
		<h2 class="green-grad"><?php _e('Success Days', 'selfcare'); ?></h2>
		<div style="border-top: 1px solid #727272;">
			<?php output_progress_widget($user_id); ?>
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


			</div>

		</div>
	</div>
</div><!-- Close: column-2 -->


<div class="aclear" style="clear: both;"></div>
</div><!--Close wrapper-->
</div><!--Close wrapper outer-->
<?php
wp_reset_query();
?>
<?php

//load previous data
if($retrack_jsondata&&$_GET['retrack']){
	?>
	<script></script>
	<script type="text/javascript">


		jQuery(document).ready(function($){


			//alert('yomama');


			var someJSON = '<?php
//php to escape the apostrophe's since people use them all teh time
			$retrack_jsondata = addcslashes($retrack_jsondata,'\'');
			echo str_replace('<br />','\\\n',$retrack_jsondata); ?>';
			var parsed = JSON.parse(someJSON);

			//console.log(parsed);

			//alert(parsed);

			mySelfCare.loadParsedData(parsed);


		});

	</script>
<?php
} else {
	?>
<script type="text/javascript">
	jQuery(document).ready(function($){

		if (doLoadData == true) { mySelfCare.loadData(); };
	});
</script>

		<?php
		} //end previous data
		?>
<script type="text/javascript">
	jQuery(document).ready(function($){

		if (doLoadData == true) { mySelfCare.loadData(); };


// front-end js to disallow user input of characters that break the JSON when it is trying to be parsed:

		jQuery('#journal-entry').keypress(function(e) {
			// Check if the value of the input is valid
			//console.log(e.keyCode );
			var valid
			switch(e.keyCode){
				case 34 : // "
				case 91 : // [
				case 92 : // \
				case 93 : // ]
				case 123 : // {
				case 124 : // |
				case 125 : // }
					valid = false;
					break;
				default:
					valid = true;
					break;
			}
			if (!valid)
				e.preventDefault();
		});
	});
</script>
