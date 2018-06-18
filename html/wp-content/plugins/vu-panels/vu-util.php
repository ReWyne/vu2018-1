
<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

//utility functions

/**
 * Print message or object to error_log
 * @param  mixed $messgae String, object, or array
 * @return boolean success
 */
function vu_log($message) {
  if ( IS_WP_DEBUG ) {
      if ( is_array($message) || is_object($message) ) {
          error_log( print_r($message, true) );
      } else {
          error_log( $message );
      }
  }
}

/**
 * Print message or object to console via PC::debug, with some extra info added
 * @param  mixed $messgae String
 * @return void
 */
function vu_pc_debug($message, ...$args){
    
    if ( ! IS_WP_DEBUG ) return;

    global $post;
    global $vu_pc_dbg_counter;
    if(!isset($vu_pc_dbg_counter)){$vu_pc_dbg_counter = 0;}
    ++$vu_pc_dbg_counter;

    $separator = " | ";

    $output = $message.$separator;
    
    if (isset($post)){
        $output .= $post->post_name.$separator;
    }
    else{
        $output .= "[no post]".$separator;
    }

    $output .= "counter: ".$vu_pc_dbg_counter.$separator.var_export($args, true); //for nicer formatting, use vu_echo_to_str('print_r', $args)

    PC::debug($output);
    return;
}

/**
 * Catch a printing function and return a string instead
 * @param  function $func, $params
 * @return string output
 * example usage: vu_echo_to_str('echoing_function', $arg1, $arg2);
 */
function vu_echo_to_str($func, ...$params){
    ob_start();
    $func(...$params);
    $output = ob_get_clean();
    //ob_end_flush(); //unneeded, should be flushed when function returns
    return $output ;
}

/**
 * Check if a post is a custom post type.
 * @param  mixed $post Post object, ID, array, or post type as a string/array of strings
 * @return boolean
 */
function vu_is_custom_post_type( $post = NULL )
{
    $all_custom_post_types = get_post_types( array ( '_builtin' => FALSE ) );

    // there are no custom post types
    if ( empty ( $all_custom_post_types ) )
        return FALSE;

    $custom_types = array_keys( $all_custom_post_types );

    //if array was passed, check if anything in it is a custom post type
    if(is_array($post)){
        return array_intersect($post, $custom_types);
    }

    //if string was passed, assume it is the post type. if object was passed, get its post type
    if (is_string($post)){
        $current_post_type = $post;
    }
    else{
        $current_post_type = get_post_type( $post );
    }

    // could not detect current type
    if ( ! $current_post_type )
        return FALSE;

    return in_array( $current_post_type, $custom_types );
}

