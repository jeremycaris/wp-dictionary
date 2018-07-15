<?php
/*

  Plugin Name: Dictionary

  Description: A utility plugin that adds a dictionary post type, modifies searches to only match whole words or exact searches, enforces full post display on category pages, sorts the posts by title, and adds a menu for entries via the shortcode [dictionary_menu].

  Author:      Jeremy Caris
  
  Author URI:   https://714web.com/

  Version:     1.0

  Category:     utility

*/

if (!defined('ABSPATH')) exit();


require 'checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/jeremycaris/sofw-dictionary/',
	__FILE__,
	'sofw-dictionary'
);


wp_enqueue_style( 'style', plugins_url( '/assets/css/style.css', __FILE__ ) );


function pluginprefix_install()
{
    // register custom post type
    register_dictionary_cpt();
    
    // register category taxonomy
    cptui_register_category();
    
    // create default category terms A-Z
    register_default_cats();
 
    // clear the permalinks after registering custom post type
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pluginprefix_install' );




function register_default_cats() {
    $alphabetLabel = range('A', 'Z');

    foreach($alphabetLabel as $value) {
        if (!term_exists( $value, 'dictionary_category') ){
            wp_insert_term( $value, 'dictionary_category' );
        }
    }
}




add_action( 'init', 'register_dictionary_cpt' );
function register_dictionary_cpt() {
	$labels = array(
		"name" => __( 'Dictionary', '' ),
		"singular_name" => __( 'Dictionary', '' ),
		"menu_name" => __( 'Dictionary', '' ),
		"all_items" => __( 'Dictionary Entries', '' ),
		);

	$args = array(
		"label" => __( 'Dictionary', '' ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => true,
		"show_in_menu" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"rewrite" => array( "slug" => "dictionary", "with_front" => true ),
		"query_var" => true,
		'menu_icon'   => 'dashicons-book-alt',
		"supports" => array( "title", "editor", "thumbnail" ),		
		"taxonomies" => array( "dictionary_category" ),
			);
	register_post_type( "dictionary", $args );
}




add_action( 'init', 'cptui_register_category' );
function cptui_register_category() {
	$labels = array(
		"name" => __( 'Dictionary Categories', '' ),
		"singular_name" => __( 'Dictionary Category', '' ),
		);

	$args = array(
		"label" => __( 'Dictionary Categories', '' ),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => true,
		"label" => "Dictionary Categories",
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'dictionary_category', 'with_front' => true, ),
		"show_admin_column" => false,
		"show_in_rest" => false,
		"rest_base" => "",
		"show_in_quick_edit" => false,
		'show_ui'           => true,
		'show_admin_column' => true,
	);
	register_taxonomy( "dictionary_category", array( "dictionary" ), $args );
}




// Show full content instead of excerpt in dictionary archives
function full_content_in_custom_post_archive($content = false) {
	if ( is_post_type_archive("dictionary") || is_tax('dictionary_category') ) :
		global $post;
		$content = $post->post_excerpt;
	// If an excerpt is set in the Optional Excerpt box
		if($content) :
			$content = apply_filters('the_excerpt', $content);
	// If no excerpt is set
		else :
			$content = $post->post_content;
		endif;
    endif;

	return $content;
}
add_filter('the_content', 'full_content_in_custom_post_archive');




// Order dictionary post type archive by title instead of date
function my_orderby_filter($orderby, &$query){
    global $wpdb;
	$post_type = get_query_var('post_type');

    if ($post_type == "dictionary" || is_tax('dictionary_category')) {
         return "$wpdb->posts.post_title ASC";
    }

    return $orderby;
 }
add_filter("posts_orderby", "my_orderby_filter", 10, 2);




// Show all entries in each category of dictionary custom post type
function set_posts_per_page_for_custom_post_type_cats( $query ) {
	$taxes = get_object_taxonomies('dictionary');

	if(count($taxes) > 0) {	
		foreach($taxes as $tax) {
            if ( is_tax( $tax ) ) {
                $query->set( 'posts_per_page', '-1' );
            }
        }
    }
}
add_action( 'pre_get_posts', 'set_posts_per_page_for_custom_post_type_cats' );




// Search only whole words or exact search (ie, searching for arm matches arm and arm-twisting but doesn't match armor or disarm)
add_filter('posts_search', 'my_search_is_exact', 20, 2);
function my_search_is_exact($search, $wp_query){
    global $wpdb;

    if(empty($search))
        return $search;

    $q = $wp_query->query_vars;
    $n = !empty($q['exact']) ? '' : '%';

    $search = $searchand = '';

    foreach((array)$q['search_terms'] as $term) :

        $term = esc_sql(like_escape($term));

        $search.= "{$searchand}($wpdb->posts.post_title REGEXP '[[:<:]]{$term}[[:>:]]') OR ($wpdb->posts.post_content REGEXP '[[:<:]]{$term}[[:>:]]')";

        $searchand = ' AND ';

    endforeach;

    if(!empty($search)) :
        $search = " AND ({$search}) ";
        $search .= " AND ($wpdb->posts.post_password = '') ";
    endif;

    return $search;
}




function generate_dictionary_menu(){
    $content;
    $home = get_home_url();
    $signup = $home . '/signup/';
    
    $terms = get_terms( array(
        'taxonomy' => 'dictionary_category',
        'hide_empty' => true,
    ) );
    
    $content .= '<div class="dictionary-menu"><ul>';
    foreach ($terms as $term) {
        $content .= '<li><a href="' . get_term_link($term) . '">' . $term->name . '</a></li>';
    }
    $content .= '</ul></div>';
    $content .= '<div class="dictionary-menu-mobile"><select onchange="location=this.value;">';
        $content .= '<option>Select Category</option>';
    foreach ($terms as $term) {
        $content .= '<option value="' . get_term_link($term) . '">' . $term->name . '</option>';
    }
    $content .= '</select></div>';
    
    echo $content;
}
add_shortcode('dictionary_menu', 'generate_dictionary_menu');

