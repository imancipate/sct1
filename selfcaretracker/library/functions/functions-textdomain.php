<?php

add_action( 'after_setup_theme', 'PurifyYourGaze_setup', 100 );
function PurifyYourGaze_setup(){
    load_child_theme_textdomain( 'PurifyYourGaze', get_stylesheet_directory() );
    load_child_theme_textdomain( 'PurifyYourGazeAdmin', get_stylesheet_directory() );
}

?>