jQuery(document).ready(function() {
	jQuery("#getStartedButton").click(function(){window.location="./tracker-settings";});
	jQuery("#toggle-template").click(function(){
		var pos = jQuery(this).position();
		var width = jQuery(this).outerWidth()+20;
		jQuery("#template-sidebar").css({
			position: "absolute",
			top: pos.top + "px",
			left: (pos.left + width) + "px"
		}).show("slow");
		return false;
	});
	jQuery(".default-value").click(function(){jQuery("#template-sidebar").hide();});
	jQuery(".default-value").each(function() {
		jQuery(".sbc-status-level2,.sbc-status-level4,.sbc-status-level6,.sbc-status-level8").attr("title","View details for this day");
		jQuery(".sbc-status-free").attr("title","Track for this day");
		jQuery(".sbc-prev-month").attr("title","View Previous Month\'s Progress");
		jQuery(".sbc-next-month").attr("title","View Next Month\'s Progress");
	});
	/* new functions by josh */
	/* function: element default values */
	jQuery("*[placeholder!='']").each(function() {
		if (String(this.value).trim() == '') { this.value = jQuery(this).attr('placeholder'); }
		jQuery(this).focus(function() { if (this.value == jQuery(this).attr('placeholder')) { this.value = ''; }; });
		jQuery(this).blur(function() { if(String(this.value).trim() == '') { this.value = jQuery(this).attr('placeholder'); }; });
	});
});