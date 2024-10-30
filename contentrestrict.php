<?php
/*
Plugin Name: Content Restrict By OTP
Plugin URI: http://amezingapps.com/
Description: Restrict content type either it is custom post type, post or page only for loged in user.
Author: Md. Meraj Ahmed
Author URI: http://amezingapps.com/
Version: 0.1
Copyright (c) 2014 Md. Meraj Ahmed 
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// load the admin section to manage admin setting 
function cr_admin_section() {
include(dirname ( __FILE__ ).'/admin/cr_admin_panel.php');
}

// create admin menu
function cr_admin_menu() {
add_menu_page('Content Restrict', 'Content Restrict', 'manage_options', 'cr-manager', 'cr_admin_section');
}

//add default value

function cr_install_value() {
  global $wpdb;
  
  $crcontenttypes = array( 'post', 'page' );
  $cr_ver = 0.1;
  add_option('cr_contentstype',$crcontenttypes);
  add_option('cr_version', $cr_ver);
}


// keep browser away from caching the pages when user not logged in
function cr_no_cache() {
	if ( !is_user_logged_in() ) {
		nocache_headers();
	}
}

// check weither content has been restricted or not
function cr_check_restrict_content($cr_content) {
global $post;
$content_id =  get_post_meta(get_the_ID(), 'cr_restrict_content', true );
$content_url = get_permalink(get_the_ID());
$showcontent = get_option('cr_content_show');
 if($content_id==1 && !is_user_logged_in())
 {
   if(is_home())
   {
   $cr_content  = '<p>This content is accessed only by loged in user, please <a href="' . get_bloginfo ( 'wpurl' ) . '/wp-login.php?redirect_to=' . urlencode($content_url)  . '">login</a> to view this content.</p>';
   }
   else
   {
     if($showcontent!='') 
	 {
     $cr_content = '<div>'.$showcontent.'</div>';
	 }else{
	 $cr_content = '<div>This content is only acessed by loged in user, please login to view</div>';
	 }
	 
	 $cr_content .='<div>'. wp_login_form('redirect='.urlencode($content_url)).'</div>';
	 
	 if ( get_option('users_can_register') )
	 {
	  $cr_content .= '	<a href="' . get_bloginfo ( 'wpurl' ) . '/wp-login.php?action=register">Register</a> | ';
	  }
     $cr_content .= '<a href="' . get_bloginfo ( 'wpurl' ) . '/wp-login.php?action=lostpassword">Lost your password?</a></p>';
	 
   }
 }
return $cr_content;
}


function cr_comment_restrict($cr_comment_array) {
global $post;
$content_id =  get_post_meta($post->ID, 'cr_restrict_content', true );
if($content_id==1)
 {
   $cr_comment_array =  array();
 }
 return $cr_comment_array;
}


////// adding meta box start here/////////////////////////////
function cr_meta_box_add()
{
   $posttypes = get_option('cr_contentstype');                                   //array('post','page');
   
   if($posttypes!='' && count($posttypes) > 0)
   {
   foreach($posttypes as $pts) {
    add_meta_box( 'cr-meta-box-id', 'Restrict Content', 'cr_meta_box_cb', $pts, 'normal', 'high' );
   }
   }	
}


function cr_meta_box_cb( $post )
{
$selected = get_post_meta($post->ID, 'cr_restrict_content', true);
// We'll use this nonce field later on when saving.
wp_nonce_field( 'cr_meta_box_nonce', 'meta_box_nonce' );
?>
<p>
<label for="cr_restrict_content">Do You Want To Restrict Its Content?</label>
        <select name="cr_restrict_content" id="cr_restrict_content">
            <option value="0" <?php selected( $selected, '0' ); ?>>No</option>
            <option value="1" <?php selected( $selected, '1' ); ?>>Yes</option>
        </select>
</p>
<?php
   
}


function cr_meta_box_save( $post_id )
{
    // Bail if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
     
    // if our nonce isn't there, or we can't verify it, bail
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'cr_meta_box_nonce' ) ) return;
     
    // if our current user can't edit this post, bail
	
    if( !current_user_can( 'edit_post' ) ) return;
     
    // now we can actually save the data
    $allowed = array(
        'a' => array( // on allow a tags
            'href' => array() // and those anchors can only have href attribute
        )
    );
     
    // Make sure your data is set before trying to save it
        if( isset( $_POST['cr_restrict_content'] ) )
        update_post_meta( $post_id, 'cr_restrict_content', esc_attr( $_POST['cr_restrict_content'] ) );
}


/////// adding metabox end here//////////////////////////////

//hook function
register_activation_hook( __FILE__, 'cr_install_value');

//Add Filter
add_filter ( 'the_content' , 'cr_check_restrict_content' , 50 );
add_filter ( 'the_excerpt' , 'cr_check_restrict_content' , 50 );
add_filter ( 'comments_array' , 'cr_comment_restrict' , 50 );

// Add Actions
add_action( 'add_meta_boxes', 'cr_meta_box_add' );
add_action('admin_menu', 'cr_admin_menu');
add_action( 'save_post', 'cr_meta_box_save' );
add_action( 'send_headers' , 'cr_no_cache' );
