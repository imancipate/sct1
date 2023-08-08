<?php

// Add a custom controller to the JSON api
// Controllers must be activated in the WordPress dashboard: Settings > JSON API.
function add_sct_controllers($controllers) {
  $controllers[] = 'scrcategory';
  $controllers[] = 'customposts';
  $controllers[] = 'options';
  $controllers[] = 'terms';
  $controllers[] = 'scrcustom';
  $controllers[] = 'progress';
  $controllers[] = 'trackersubmit';
  $controllers[] = 'progressajax';
  
  return $controllers;
}
add_filter('json_api_controllers', 'add_sct_controllers');
// set controller path
function set_scrcategory_controller_path() {
	global $sct_plugin_path, $sct_plugin_url;
    return $sct_plugin_path . "library/jsoncontrollers/scrcategory.php";
}
add_filter('json_api_scrcategory_controller_path', 'set_scrcategory_controller_path');

// set controller path
function set_customposts_controller_path() {
	global $sct_plugin_path, $sct_plugin_url;
	return $sct_plugin_path . "library/jsoncontrollers/customposts.php";
}
add_filter('json_api_customposts_controller_path', 'set_customposts_controller_path');

// set controller path
function set_options_controller_path() {
	global $sct_plugin_path, $sct_plugin_url;
    return $sct_plugin_path . "library/jsoncontrollers/options.php";
}
add_filter('json_api_options_controller_path', 'set_options_controller_path');

// set controller path
function set_terms_controller_path() {
	global $sct_plugin_path, $sct_plugin_url;
    return $sct_plugin_path . "library/jsoncontrollers/terms.php";
}
add_filter('json_api_terms_controller_path', 'set_terms_controller_path');


// set controller path
function set_scrcustom_controller_path() {
	global $sct_plugin_path, $sct_plugin_url;
    return $sct_plugin_path . "library/jsoncontrollers/scrcustom.php";
}
add_filter('json_api_scrcustom_controller_path', 'set_scrcustom_controller_path');


// set controller path
function set_progress_controller_path() {
	global $sct_plugin_path, $sct_plugin_url;
    return $sct_plugin_path . "library/jsoncontrollers/progress.php";
}
add_filter('json_api_progress_controller_path', 'set_progress_controller_path');


// set controller path
function set_trackersubmit_controller_path() {
	global $sct_plugin_path, $sct_plugin_url;
    return $sct_plugin_path . "library/jsoncontrollers/trackersubmit.php";
}
add_filter('json_api_trackersubmit_controller_path', 'set_trackersubmit_controller_path');

// set controller path
function set_progressajax_controller_path() {
	global $sct_plugin_path, $sct_plugin_url;
    return $sct_plugin_path . "library/jsoncontrollers/progressajax.php";
}
add_filter('json_api_progressajax_controller_path', 'set_progressajax_controller_path');

?>