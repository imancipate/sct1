<?php
/*
Template Include: SelfCare Tracker Settings
*/

$tmpAddiction = __('Enter any compulsive behaviors you intend to abstain from that threaten your self-care.', 'selfcare');
$strAddiction = __($tmpAddiction, 'selfcare');
$defaultAddiction = json_encode(__($tmpAddiction, 'selfcare'));
$tmpCommitment = __('Enter why you are committed to your self care today.','selfcare');
$strCommitment = __($tmpCommitment, 'selfcare');
$defaultCommitment = json_encode(__($tmpCommitment, 'selfcare'));
?>
<script type="text/javascript">
//User Interface

jQuery(document).ready(function(){

	jQuery('#tracker-nav-setup').addClass('active');
	jQuery('#main-tracker-info').accordion({
		heightStyle: "content"
	});

	jQuery('#main-tracker-info .next-button').each(function() { jQuery(this).click(function() {
		var nextAccordianIndex = jQuery( "div.ui-accordion-content" ).index(this.parentNode.parentNode)+1;
		jQuery('#main-tracker-info').accordion('option','active', nextAccordianIndex);
		jQuery('.column-2 .tip').each(function() { jQuery(this).hide(); });
		jQuery('#'+getnextsibling(this.parentNode.parentNode).id+'-tip').fadeIn(2000);
	}); });
	jQuery('#main-tracker-info .previous-button').each(function() { jQuery(this).click(function() {
		var prevAccordianIndex = jQuery( "div.ui-accordion-content" ).index(this.parentNode.parentNode)-1;
		jQuery('#'+this.parentNode.parentNode.parentNode.id).accordion('option', 'active', prevAccordianIndex);
		jQuery('.column-2 .tip').each(function() { jQuery(this).hide(); });
		jQuery('#'+getprevioussibling(getprevioussibling(getprevioussibling(this.parentNode.parentNode))).id+'-tip').fadeIn(2000);
	}); });
	jQuery('#main-tracker-info>h2').each(function() { jQuery(this).click(function() {
		jQuery('.column-2 .tip').each(function() { jQuery(this).hide(); });
		jQuery('#'+this.id+'-tip').fadeIn(2000);
	}); });
	jQuery('.column-2 .tip').each(function() { jQuery(this).hide(); });
	jQuery('#my-commitment-tip').fadeIn(2000);
	jQuery('#main-tracker-info textarea').elastic();
	jQuery('#new-ritual-form textarea').elastic();
	jQuery('.finished-button').each(function() { jQuery(this).click(function() { window.location = '<?php echo get_bloginfo('url'); ?>/tracker<?php echo $user_query; ?>'; }); });

});
// ## View Model ##
function Ritual(data) {
	var data = data || {};
	this.id = ko.observable(data.id || '');
	this.category = ko.observable(data.category || '').extend({editable: true});
	this.title = ko.observable(data.title || '').extend({editable: true});
	this.description = ko.observable(data.description || '').extend({editable: true});
	ko.editable(this);
}
var mySelfCare = {
	userID: ko.observable(<?php echo $user_id; ?>),
	addiction: ko.observable(''),
	commitment:  ko.observable(''),
	rituals: ko.observableArray([]),
	selectedRitual: ko.observable(new Ritual()),
	editingRitual: ko.observable(false),
	state: ko.observable('behaviour'),
	categories: ko.observableArray([]),
	selectedCategory: ko.observable(''),
	action: ko.observable(''),
	ritualID: ko.observable(''),
	ritualCount: ko.observable(''),
	ritualTemplates: ko.observableArray([]),
	selectedRitualTemplate: ko.observable(new Ritual())
}; jQuery(document).ready(function(){ ko.applyBindings(mySelfCare); });
mySelfCare.selectedRitualTemplate.subscribe(function(selectedTemplate){ if (selectedTemplate[0]) { mySelfCare.selectedRitual(selectedTemplate[0]); } else { mySelfCare.selectedRitual(new Ritual()); }; }, 'afterChange');
mySelfCare.selectedCategory.subscribe(function(selectedCat){ mySelfCare.loadTemplates(selectedCat[0]); }, 'afterChange');
// ## Actions and Click Handlers ##
mySelfCare.addRitual = function() {
	var ritualValidated = true;
	if (this.title() == '' || this.title() == '<?php _e('Enter a short title for the behavior.', 'selfcare'); ?>') { ritualValidated = false; alert('<?php _e('Please enter a short title for the behavior.', 'selfcare'); ?>'); };
	if (ritualValidated != false && (this.description() == '' || this.description() == '<?php _e('Describe a behavior that helps you abstain from your addiction(s).', 'selfcare'); ?>')) { ritualValidated = false; alert('<?php _e('Please describe a behavior that helps you abstain from your addiction(s).', 'selfcare'); ?>'); };
	if (ritualValidated == true) {
		mySelfCare.lastUpdated = this;
		mySelfCare.rituals.push(this);
		mySelfCare.selectedRitual(new Ritual());
		mySelfCare.action('addRitual');
		mySelfCare.ritualCount(mySelfCare.rituals().length);
		mySelfCare.saveData();
	};
};
mySelfCare.removeRitual = function() {
	if (confirm('<?php _e('Delete this Ritual?', 'selfcare'); ?>')) {
		var tmpID = ''+this.id();
		mySelfCare.rituals.remove(this);
		mySelfCare.action('deleteRitual');
		mySelfCare.ritualID(tmpID);
		mySelfCare.ritualCount(mySelfCare.rituals().length);
		mySelfCare.saveData();
	};
};
mySelfCare.editRitual = function() {
	this.category.beginEdit();
	this.title.beginEdit();
	this.description.beginEdit();
	mySelfCare.selectedRitual(this)
	mySelfCare.editingRitual(true);
};
mySelfCare.cancelEditRitual = function() {
	var tmpID = ''+this.id();
	this.category.rollback();
	this.title.rollback();
	this.description.rollback();
	mySelfCare.selectedRitual(new Ritual());
	mySelfCare.editingRitual(false);
};
mySelfCare.updateRitual = function() {
	var ritualValidated = true;
	if (this.title() == '' || this.title() == '<?php _e('Enter a short title for the behavior.', 'selfcare'); ?>') { ritualValidated = false; alert('<?php _e('Please enter a short title for the behavior.', 'selfcare'); ?>'); };
	if (ritualValidated != false && (this.description() == '' || this.description() == '<?php _e('Describe a behavior that helps you abstain from your addiction(s).', 'selfcare'); ?>')) { ritualValidated = false; alert('<?php _e('Please describe a behavior that helps you abstain from your addiction(s).', 'selfcare'); ?>'); };
	if (ritualValidated == true) {
		var tmpID = ''+this.id();
		this.category.commit();
		this.title.commit();
		this.description.commit();
		mySelfCare.selectedRitual(new Ritual());
		mySelfCare.editingRitual(false);
		mySelfCare.action('updateRitual');
		mySelfCare.ritualID(tmpID);
		mySelfCare.saveData();
	};
};
mySelfCare.saveOrder = function() {
	mySelfCare.action('updateOrder');
	mySelfCare.saveData();
};
mySelfCare.saveMeta = function() {
	mySelfCare.action('updateMeta');
	mySelfCare.saveData();
};
// ## Data Loading and Saving ##
mySelfCare.saveData = function() {
	// Remove Default Values
	if (mySelfCare.addiction() == <?php echo $defaultAddiction; ?> || mySelfCare.addiction() == 'undefined' || mySelfCare.addiction() == undefined) { mySelfCare.addiction(''); };
	if (mySelfCare.commitment() == <?php echo $defaultCommitment; ?> || mySelfCare.commitment() == 'undefined' || mySelfCare.commitment() == undefined) { mySelfCare.commitment(''); };
	// ToDo Save;
	if (mySelfCare.action() == 'addRitual') {
		// send ajax request to JSON API controller at: wp-content\plugins\selfcaretracker\library\jsoncontrollers
		var mySave = jQuery.post("<?php echo get_bloginfo('url');?>/api/trackersubmit/submitsetup/", ko.toJSON(mySelfCare), function(data) { if (mySelfCare.lastUpdated.id() == '') { mySelfCare.lastUpdated.id(String(String(data).trim()).replace('addRitual','')); }; });
	} else {
		// send ajax request to JSON API controller at: wp-content\plugins\selfcaretracker\library\jsoncontrollers
		var mySave = jQuery.post("<?php echo get_bloginfo('url');?>/api/trackersubmit/submitsetup/", ko.toJSON(mySelfCare));
	};
	//jQuery('#my-addictions-tip').html(ko.toJSON(mySelfCare));
	// Reset Default Values
	//if (mySelfCare.addiction() == '') { mySelfCare.addiction(<?php echo $defaultAddiction; ?>); };
	//if (mySelfCare.commitment() == '') { mySelfCare.commitment(<?php echo $defaultCommitment; ?>); };
	mySelfCare.action('');
	mySelfCare.ritualID('');
	/*
	jQuery('#categories option:selected').removeAttr('selected');

	jQuery('#categories option').first('option').attr('selected', 'selected');
	jQuery('#templates option:selected').removeAttr('selected');
	jQuery('#templates option').first('option').attr('selected', 'selected');
	jQuery('#categories').change();
	*/
};
var doLoadData = true;

//var doLoadData = false;


mySelfCare.loadData = function() {
	// ToDo Load Data;
	mySelfCare.addiction(<?php if (get_user_meta($user_id, 'sc_addiction', true) != '') { echo json_encode(get_user_meta($user_id, 'sc_addiction', true)); } else { echo ''; };// else { echo $defaultAddiction; }; ?>);
	mySelfCare.commitment(<?php if (get_user_meta($user_id, 'sc_commitment', true) != '') { echo json_encode(get_user_meta($user_id, 'sc_commitment', true)); } else { echo ''; };// else { echo $defaultCommitment; }; ?>);
	jQuery.getJSON('<?php echo get_bloginfo('url'); ?>/api/scrcustom/get_author_posts/?author_id=<?php echo $user_id; ?>&post_type=scr_custom&orderby=menu_order&order=ASC&dev=1', function(data) {
		mySelfCare.rituals([]);
		jQuery.each(data.posts, function() {
			var tmpRitual = {};
			tmpRitual.id = this.id;
			tmpRitual.category = this.scr_category[0];
			var tmpTitle = this.title;
			var tmpDecodedTitle = jQuery('<div/>').html(tmpTitle).text();
			tmpRitual.title = tmpDecodedTitle;
			tmpRitual.description = this.excerpt;
			mySelfCare.rituals.push(new Ritual(tmpRitual));
		});
		mySelfCare.ritualCount(mySelfCare.rituals().length);
	});
	jQuery.getJSON('<?php echo get_bloginfo('url'); ?>/api/terms/get_terms_index/?tax=scr_category&dev=1', function(data) {
		mySelfCare.categories([]);
		jQuery.each(data.terms, function() {
			mySelfCare.categories.push(this.title);
		});
		mySelfCare.loadTemplates(mySelfCare.categories()[0]);
	});
	jQuery('#main-tracker-info textarea').elastic();
	jQuery('#new-ritual-form textarea').elastic();
}; jQuery(document).ready(function(){
	if (doLoadData == true) { mySelfCare.loadData();};
});

mySelfCare.hideTemplates = true;
mySelfCare.loadTemplates = function(forCategory) {
	jQuery.getJSON('<?php echo get_bloginfo('url'); ?>/api/scrcustom/get_author_posts/?author_id=1&post_type=scr_template&post_status=publish&order=ASC&dev=1', function(data) {
		mySelfCare.ritualTemplates([]);
		jQuery.each(data.posts, function() {
			var tmpRitual = {};
			tmpRitual.id = this.id;
			tmpRitual.category = this.scr_category[0];
			var tmpTitle = this.title;
			var tmpDecodedTitle = jQuery('<div/>').html(tmpTitle).text();
			tmpRitual.title = tmpDecodedTitle;
			tmpRitual.description = this.excerpt;
			if (tmpRitual.category == forCategory) { mySelfCare.ritualTemplates.push(new Ritual(tmpRitual)); };
		});
		if (mySelfCare.hideTemplates) { jQuery('#scr_templates').hide(); }
		//jQuery('#scr_templates').hide();
		jQuery('#scr_template_list').show();
		jQuery('#sct-cat-ajax-spinner').hide();
	});
};
// ## Other ##
//jQuery(document).ready(function(){ jQuery('#scr_templates').hide(); });
jQuery('.noEnterSubmit').keypress(function(e){
	if ( e.which == 13 ) return false;
	if ( e.which == 13 ) e.preventDefault();
});
</script>
<script type="text/javascript" id="sct_final_js">

	<?php
	do_action('sct_final_js');
	?>

</script>

<div class="column-1">
	<?php if($displayed_user_data&&current_user_can('administrator')){ ?>
		<h3 id="setupfor"><?php _e('Setup for : ', 'selfcare');
			echo $displayed_user_data->user_nicename;
			echo '<a href="'.get_bloginfo('url').'/wp-admin/users.php" target="_blank" id="change_user" title="Change displayed user"class="change_user_icon tracker_button"></a>';
			?>
		</h3>
	<?php } //endif ?>

	<div id="main-tracker-info" class="box accordian">

		<h2 id="my-commitment"><a href="#">1. <?php _e('Why I\'m Committed To My Self-Care Today', 'selfcare'); ?></a></h2>
		<div style="height:inherit;">
			<div class="inline-tip">
				<p><?php _e('What is your driving purpose and motivation to adding fulfillment to your life and making your well-being important each day?', 'selfcare'); ?></p>
			</div>
			<textarea class="edit-input" id="my-commitment-textarea" name="my-commitment-textarea" data-bind="value: commitment, event: { change: $root.saveMeta }" placeholder=<?php echo $defaultCommitment; ?>></textarea>


			<div class="action-buttons"><button class="next-button"><?php _e('Next', 'selfcare'); ?></button></div>
		</div>
		<h2 id="my-addictions"><a href="#">2. <?php _e('Behaviors That Threaten My Self-Care', 'selfcare'); ?></a></h2>
		<div>
			<div class="inline-tip">
				<p><?php _e('The foundation to your self-care involves abstaining from behaviors that threaten your personal and spiritual well-being and you will no longer tolerate in your life.', 'selfcare'); ?></p>
			</div>
			<textarea class="edit-input" id="my-addictions-textarea" name="my-addictions-textarea" data-bind="value: addiction, event: { change: $root.saveMeta }" placeholder=<?php echo $defaultAddiction; ?>></textarea>


			<div class="action-buttons"><button class="previous-button"><?php _e('Previous', 'selfcare'); ?></button><button class="next-button"><?php _e('Next', 'selfcare'); ?></button></div>
		</div>
		<h2 id="my-selfcare"><a href="#">3. <?php _e('My Self-Care Rituals', 'selfcare'); ?></a></h2>
		<div>
			<ul id="my-ritual-list-edit" data-bind="sortable: { data: rituals, afterMove: $root.saveOrder }">
				<li data-bind="attr: { 'id': id }, css: {selected: $data == $root.selectedRitual()}" class="my-ritual drag-handle" title="Click / Drag items to change the order this appears in your daily list">
					<div class="normal-display">
						<h3><span data-bind="text: title" class="my-ritual-title"></span> <span class="edit-buttons"><a class="edit-button" title="<?php _e('Edit', 'selfcare'); ?>" data-bind="click: $root.editRitual"><?php _e('Edit', 'selfcare'); ?></a> <a class="remove-button" title="<?php _e('Remove', 'selfcare'); ?>" data-bind="click: $parent.removeRitual"><?php _e('Remove', 'selfcare'); ?></a></span></h3>
						<p><em data-bind="text: description"></em></p>
					</div>
					<div class="edit-form">
						<form class="noEnterSubmit" onsubmit="return false;">
							<h3>
								<span><?php _e('Editing self-care ritual:', 'selfcare'); ?></span>
								<select name="categories" data-bind="options: $root.categories, value: category"></select>
							</h3>
							<input class="ritual-title" placeholder="<?php _e('Enter a short title for the behavior.', 'selfcare'); ?>" data-bind="value: title" />
							<textarea class="ritual-description" placeholder="<?php _e('Describe how this behavior supports your self-care.', 'selfcare'); ?>" data-bind="value: description"></textarea>
							<div class="action-buttons"><button type="button" data-bind="click: $root.cancelEditRitual"><?php _e('Cancel Edit', 'selfcare'); ?></button> <button type="button" data-bind="click: $root.updateRitual"><?php _e('Update Ritual', 'selfcare'); ?></button></div>
						</form>
					</div>
				</li>
			</ul>
			<div id="new-ritual-form" data-bind="visible: (ritualCount() < 7 && !editingRitual())">
				<form data-bind="with: selectedRitual" class="noEnterSubmit" onsubmit="return false;">
					<h3>
						<span data-bind="visible: $root.editingRitual() == false"><?php _e('Add a new self-care ritual:', 'selfcare'); ?></span>
						<span data-bind="visible: $root.editingRitual() == true"><?php _e('Editing self-care ritual:', 'selfcare'); ?></span>
					</h3>
					<p>Describe a behavior below that supports your self-care. These could be boundaries that you have in place that protect you from slips or could be positive activities that deepen the fulfillment, joy and overall quality of your life.</p>
					<h3 style="margin-top:20px;"><div id="sct-suggestions-link" onclick="jQuery('#scr_templates').toggle(); if (mySelfCare.hideTemplates) { mySelfCare.hideTemplates = false; } else { mySelfCare.hideTemplates = true; };" data-bind="visible: ($root.ritualTemplates().length > 0)"><span style="margin:0px 2px;"> ~ OR ~</span> Start from Suggestions: <span style="text-transform:lowercase;font-size:14px;font-weight:bold;text-decoration:underline;padding-left:5px;cursor:pointer;">[ show / hide ]</span></div>
						<?php _e('Enter your Ritual Below', 'selfcare'); ?>
					</h3>


					<div id="scr_templates" data-bind="visible: ($root.ritualTemplates().length > 0 && !mySelfCare.hideTemplates)">
						<h3>Category: <select id="categories" name="categories" data-bind="options: $root.categories, selectedOptions: $root.selectedCategory" onchange = "jQuery('#scr_template_list').hide();jQuery('#sct-cat-ajax-spinner').show();"></select>
							<br /><br />
						<span><?php _e('Choose from the list of suggested rituals (optional) :', 'selfcare'); ?></span>
							<div style="display:inline;position:relative;"><div id="sct-cat-ajax-spinner"></div></div>
						<ul data-bind="foreach: $root.ritualTemplates" id="scr_template_list" >
							<li><label for="" data-bind="attr: {title: description, for: 'template_'+id() }, text: title">
								</label>
								<input type="radio" name="scr_template" id="" data-bind="attr: {id: 'template_'+id(), value: id, title: description, caption: title}" onclick="jQuery('#my-new-ritual-title').attr('value',jQuery(this).attr('caption'));jQuery('#ritual-description').html(jQuery(this).attr('title'));jQuery('#ritual-description').change();jQuery('#my-new-ritual-title').change();jQuery('#scr_templates').hide();jQuery('#ritual-description').select();" /></li>
						</ul>
					</div>
					<input placeholder="<?php _e('Enter a short title for the behavior.', 'selfcare'); ?>" id="my-new-ritual-title" name="my-new-ritual-title" data-bind="value: title" />
					<textarea placeholder="<?php _e('Enter a longer description of the behavior and how it helps you stay in your zone of self-care.', 'selfcare'); ?>" id="ritual-description" name="ritual-description" data-bind="value: description"></textarea>
					<div class="action-buttons"><button type="button" data-bind="click: $root.addRitual, visible: $root.editingRitual() == false"><?php _e('Add New Ritual', 'selfcare'); ?></button> <button type="button" data-bind="click: $root.cancelEditRitual, visible: $root.editingRitual() == true"><?php _e('Cancel Edit', 'selfcare'); ?></button> <button type="button" data-bind="click: $root.updateRitual, visible: $root.editingRitual() == true"><?php _e('Update Ritual', 'selfcare'); ?></button></div>
				</form>
			</div>
			<div class="action-buttons"><button class="previous-button"><?php _e('Previous', 'selfcare'); ?></button>


				<span data-bind="visible: commitment() == ''" class="notice notice-notfinished"><?php _e('You must enter your Commitment.', 'selfcare'); ?></span>

				<span data-bind="visible: addiction() == ''" class="notice notice-notfinished"><?php _e('You must enter your Behaviors that threaten your self care.', 'selfcare'); ?></span>

				<span data-bind="visible: ritualCount() < 5 " class="notice notice-notfinished"><?php _e('You must add at least 5 Self Care Rituals.', 'selfcare'); ?></span>

				<span data-bind="visible: (ritualCount() > 4 && ritualCount() <7 &&  addiction() != '' && commitment() != '')" class="notice notice-finished"><?php _e('Add up to a maximum of 7 Rituals.', 'selfcare'); ?></span>

				<span data-bind="visible: (ritualCount() == 7 && addiction() != '' &&  commitment() != '')" class="notice notice-finished"><?php _e('All setup steps completed.', 'selfcare'); ?></span>

				<button class="finished-button" data-bind="visible: (ritualCount() > 4  && addiction() != '' && commitment() != '')"><?php _e('Done', 'selfcare'); ?></button>

			</div>
		</div>
	</div><!-- Close: main-tracker-info -->
</div><!-- Close: column-1 -->
<div class="column-2">
	<div id="my-selfcare-tip-global"><?php dynamic_sidebar('sct-setup-sidebar-global'); ?></div>
	<div id="my-commitment-tip" class="tip"><?php dynamic_sidebar('sct-setup-sidebar-1'); ?></div>
	<div id="my-addictions-tip" class="tip"><?php dynamic_sidebar('sct-setup-sidebar-2'); ?></div>
	<div id="my-selfcare-tip" class="tip"><?php dynamic_sidebar('sct-setup-sidebar-3'); ?></div>

</div><!-- Close: column-2 -->
