
<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

//this file provides various utility functions

global $vu_print_oneline; //replaces \n's with <br>'s or whatever in output
$vu_print_oneline = true;
global $vu_print_oneline_replace_text; //what to replace the \n's with
$vu_print_oneline_replace_text = "\n";//'<br \>'
global $vu_to_str_uses; //options: print_r, var_dump
$vu_to_str_uses = 'print_r';

abstract class vu_debug_type
{
    const err_log = 'err_log';
    const pc_dbg = 'pc_dbg';
}


/**
 * Print message or object to error_log
 * @param  mixed $message String, object, or array
 * @return void
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
 * Print message or object to all available outputs, with some extra info added
 * @param  mixed $message String
 * @return void
 */
function vu_dbg($message, ...$args){
    vu_debug($message, 'all', ...$args);
}

/**
 * replace \n and \r with the specified replacement string (default '<br \>')
 * @param  string String
 * @param  string Replacement for \n's
 * @return string
 */
function vu_no_newlines($string, $replace = '<br \>'){
    //return preg_replace("/\\[nr]/", $replace, $string);
    return str_replace( ["\r\n", "\r", "\n"], $replace, $string );
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

    //initial message
    if( ! is_string($message) ){
        $output = "[".gettype($message)."] ".vu_to_str($message).$separator;
    }
    else {
        $output = $message.$separator;
    }

    //post name, if applicable
    $output .= (isset($post) ? $post->post_name : "[no post]").$separator;

    //number of times function has been called this page load
    $output .= "counter: ".$vu_pc_dbg_counter;
    
    //print additional args
    foreach($args as $arg){
        $output .= $separator.vu_to_str($arg); //or var_export($args, true) 
    }

    global $vu_print_oneline;
    global $vu_print_oneline_replace_text;
    if($vu_print_oneline == true){
        $output = vu_no_newlines($output, $vu_print_oneline_replace_text);
    }

    //handle shorthand methods of specifying output type
    if( $loggers == '' || $loggers == 'all' || $loggers == 'default' || $loggers == 'both'){
        $loggers = array('err_log','pc_dbg');
    }

    //output
    if(in_array(vu_debug_type::pc_dbg,$loggers) && class_exists("PC"))
        PC::debug($output);
    if(in_array(vu_debug_type::err_log,$loggers))
        error_log($output);
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
    return $output;
}

/**
 * Convert the provided object into a string, using method specified in the $vu_to_str_uses global
 * Note that in the specific case of print_r and a single input that would evaluate to '', the variable type is returned instead. (so pasing in NULL would print '(empty str of type: NULL' rather than '') This is helpful for debugging, but could break your code if you tried to use this as an alternative to JSON.stringify. So...don't do that.
 * @param  multiple $params
 * @return string output
 */
function vu_to_str(...$params){
    global $vu_to_str_uses;
    if($vu_to_str_uses == 'print_r'){
        $to_print = (count($params) == 1) ? array_values($params)[0] : $params;
        $out = print_r($to_print, true);
        return ($out != '') ? $out : "(empty str of type: ".gettype($to_print).")";
    }
    else{
        return vu_echo_to_str($vu_to_str_uses,...$params); //this also works for var_dump
    }
}

/**
 * Check if a post is a custom post type.
 * @param  object|int|array|string $post Post object, ID, array, or post type as a string/array of strings
 * @return boolean
 */
function vu_is_custom_post_type( $post = NULL )
{
    if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("vu_is_custom_post_type \$post ",$post);}
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
 * Useful for checking array containment/intersection
 * Ex return value - {"name1"=>3, "name2"=>55, ...} might then by checked by array_key_exists("name1", $output_array)
 * The values returned in the array are the ids of the corresponding taxonomy terms. Which *should* always be integers evaluating to true. You can get the associated term with get_term( $term_id, $taxonomy );
 * Note: if a regular array is desired instead of a set, wp provides a function comparable to this: wp_list_pluck( $subcategory_terms, 'term_id' );
 *  @param  array $term_array
 * @param  string $term_field ex - "name" or "term_id"
 * @return array $setlike_array
 */
function vu_terms_array_to_set( $term_array, $term_field ){
    if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("vu_terms_array_to_set \$term_array, \$term_field ",$term_array, $term_field);}
    $setlike_array = array();
    foreach($term_array as $term_object){
        $setlike_array[$term_object->$term_field] = $term_object->term_id;
    }
    return $setlike_array;
}

/**
 * Returns true if the two sets (arrays) provided share at least one key. Values are ignored.
 * @param  array $left_terms
 * @param  array $right_terms
 * @return boolean $intersect
 */
function vu_check_set_intersection($left_terms, $right_terms){
    //allow someone to pass in just a single key as the left-hand term only
    if( ! is_array($left_terms) ){
        $left_terms = [$left_terms => true ];
        vu_dbg("Warning: vu_check_set_intersection $left_terms is not an array");
    }

    foreach($left_terms as $lterm){
        if ( array_key_exists($lterm, $right_terms) ){
            return true;
        }
    }
    return false;
}

/**
 * Returns an array of the intersecting keys of two sets (arrays). Values are ignored.
 * @param  array $left_terms
 * @param  array $right_terms
 * @return array $intersection
 */
function vu_get_set_intersection($left_terms, $right_terms){
    if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("vu_get_set_intersection \$left_terms, \$right_terms ",$left_terms, $right_terms);}
    //allow someone to pass in just a single key as the left-hand term only
    foreach([$left_terms, $right_terms] as &$term){
        if( ! is_array($term) ){
            $term = [$term => true ];
            vu_dbg("Warning: vu_check_set_intersection parameter $term is not an array");
        }
    }

    $intersection = [];
    foreach($left_terms as $lterm){
        if ( array_key_exists($lterm, $right_terms) ){
            $intersection->append($lterm);
        }
    }
    if(VU_RESTRICT_DEBUG_LEVEL(2)){vu_dbg("vu_get_set_intersection \$intersection ",$intersection);}
    return $intersection;
}

/**
 * Get *original* WP_terms attached to object, dereferencing "pointer terms"
 * @param  int|object $object
 * @param  string $taxonomy
 * @return array $real_terms_array
 */
function vu_get_real_object_terms( $object, $taxonomy ){
    $terms = wp_get_object_terms($object, $taxonomy);
    
    //vu_debug("vu_get_real_object_terms");
    foreach($terms as &$term_object){
        //vu_debug(gettype($term_object->name),'',$term_object->name);
        while(is_numeric($term_object->name)){
            $term_object = get_term( $term_object->name, $taxonomy );
            //vu_debug("got term: ",'',$term_object);
        }
    }

    //vu_debug($terms);
    if(VU_RESTRICT_DEBUG_LEVEL(1)){vu_dbg("vu_get_real_object_terms \$terms ",$terms);}
    return $terms;
}

/**
 * Filters the found terms.
 *
 * @param array		$options      'taxonomy' specifies the taxonomy, 'hide_empty' specifies whether currently unused terms (with count == 0) are displayed
 * @return array 	$real_terms Array of found terms, filtering out reference terms. (terms that serve as a pointer to another)
 */
function vu_get_real_terms($options){
	//example:
	//get_terms( array(
	// 	'taxonomy' => 'vu_user_group',
	// 	'hide_empty' => false,  ) );
	$terms = get_terms( $options );

	foreach($terms as $term_key => $term_object){
		if( is_numeric($term_object->name) ){
			unset($terms[$term_key]);
		}
    }
    
    if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg("vu_get_real_terms",$terms);

	return array_values($terms); //reindex array before returning
}

/**
 * Returns an array of the intersecting keys of the two objects' sub-taxonomies. Values are ignored.
 * @param  int $left_id
 * @param  int $right_id
 * @param  string $taxonomy Name of taxnonomy
 * @param  int $term_field Name of field to use in the comparison 
 * @return array $intersection Array of shared terms
 */
function vu_get_object_tax_intersection($left_id, $right_id, $taxonomy, $term_field){
       //get term (should be singular!) associated with post
   $left_terms = vu_terms_array_to_set( vu_get_real_object_terms( $left_id, $taxonomy ), $term_field );
   
   //get terms associated with user
   $right_terms = vu_terms_array_to_set( vu_get_real_object_terms( $right_id, $taxonomy ), $term_field );
   if(VU_RESTRICT_DEBUG_LEVEL(1)) vu_dbg("vu_get_object_tax_intersection \$left_terms, \$right_terms", $left_terms, $right_terms);

   return vu_get_set_intersection($left_terms, $right_terms);
}