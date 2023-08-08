<?php
// this file defines custom sidebar areas and widgets for self-care tracker
// add setup sidebars
register_sidebar(array(
	'name' => __('SCT Setup - Step 1'),
	'id' => 'sct-setup-sidebar-1',
	'description' => __('Widgets in this area will be shown on the right-hand side of the setup screen, when Step 1 is active'),
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '<h2>', 
	'after_title' => '</h2>'
));
register_sidebar(array(
	'name' => __('SCT Setup - Step 2'),
	'id' => 'sct-setup-sidebar-2',
	'description' => __('Widgets in this area will be shown on the right-hand side of the setup screen, when Step 2 is active'),
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '<h2>',
	'after_title' => '</h2>'
));
register_sidebar(array(
	'name' => __( 'SCT Setup - Step 3' ),
	'id' => 'sct-setup-sidebar-3',
	'description' => __('Widgets in this area will be shown on the right-hand side of the setup screen, when Step 3 is active'),
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '<h2>',
	'after_title' => '</h2>'
));
register_sidebar(array(
		'name' => __( 'SCT Setup - Global' ),
		'id' => 'sct-setup-sidebar-global',
		'description' => __('Widgets in this area will be shown on the right-hand side of the setup screen, during all steps'),
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '<h2>',
		'after_title' => '</h2>'
));

			
class sct_widget_disable_intro_screen extends WP_Widget {
          
		  	function __construct() {
				parent::__construct(
					'sct_disable_intro', // Base ID
					'SCT Disable Intro Screen', // Name
					array( 'description' =>'SCT Disable Intro Screen', ) // Args
				);
			}
		  
		  function sct_widget_disable_intro_screen() {
				$widget_ops = array(
				'classname' => 'sct_disable_intro',
				'description' => 'SCT Disable Intro Screen'
				);
				$this->WP_Widget(
				'sct_disable_intro',
				'SCT Disable Intro Screen',
				$widget_ops
				);
			}

	          
			function widget($args, $instance) { // widget sidebar output
				echo do_shortcode('[intro-disable]');
			}
}

add_action(
		  'widgets_init',
		  create_function('','return register_widget("sct_widget_disable_intro_screen");')
);

?>