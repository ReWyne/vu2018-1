
<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

//utility functions

abstract class vu_debug_type
{
    const err_log = 'err_log';
    const pc_dbg = 'pc_dbg';
}


/**
 * Print message or object to error_log
 * @param  mixed $messgae String, object, or array
 * @return boolean success
 */
function vu_log($message, ...$args) {
    vu_debug($message, array(vu_debug_type::err_log), ...$args);
}

/**
 * Print message or object to console via PC::debug, with some extra info added
 * @param  mixed $message String
 * @return void
 */
function vu_pc_debug($message, ...$args){
    vu_debug($message, array(vu_debug_type::pc_dbg), ...$args);
}

/**
 * Print debug message via some output
 * @param  mixed String $message, array of enums (err_log, pc_dbg) $logger, other classes to output ...$args
 * @return void
 */
function vu_debug($message, $loggers = array('err_log','pc_dbg'), ...$args){
    
    if ( ! IS_WP_DEBUG ) return;

    global $post;
    global $vu_pc_dbg_counter;
    if(!isset($vu_pc_dbg_counter)){$vu_pc_dbg_counter = 0;}
    ++$vu_pc_dbg_counter;

    $separator = " | ";

    $output;

    //if $message is the (string) name of the variable to log
    if($loggers == 'var'){
        global $$message;
        $output = "$".$message.": ".print_r($$message, true).$separator;
    }
    //if you just threw an object into the first arg, quietly handle it without complaining
    elseif ( is_array($message) || is_object($message) ) {
        $output = print_r($message, true).$separator;
    } else {
        $output = $message.$separator;
    }

    if (isset($post)){
        $output .= $post->post_name.$separator;
    }
    else{
        $output .= "[no post]".$separator;
    }

    $output .= "counter: ".$vu_pc_dbg_counter;
    
    if(!empty($args)){
        $output .= $separator.print_r($args, true); //or more conventionally, var_export($args, true) 
    }
    if($loggers == '' || $loggers == 'all' || $loggers == 'default' || $loggers == 'both' || $loggers == 'var'){
        $loggers = array('err_log','pc_dbg');
    }
    if(in_array(vu_debug_type::pc_dbg,$loggers))
        PC::debug($output);
    if(in_array(vu_debug_type::err_log,$loggers))
        error_log($output);
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
 * @param  object|int|array|string $post Post object, ID, array, or post type as a string/array of strings
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

/**
 * Convert WP_terms array into array suitable only for checking (in O(1)) if a term is present, based on the specified property.
 * Ex return value - {"name1"=>true, "name2"=>true, ...} might then by checked by array_key_exists("name1", $output_array)
 * @param  array $term_array
 * @param  string $term_field ex - "name" or "term_id"
 * @return array $setlike_array
 */
function vu_terms_array_to_set( $term_array, $term_field ){
    $setlike_array = array();
    foreach($term_array as $term_object){
        $setlike_array["$term_object->$term_field"] = true;
    }
    return $setlike_array;
}