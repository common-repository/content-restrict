<?php
global $wpdb;
if(isset($_POST['submit']))
{

$posttypes = $_POST['cr_restrict_type'];
$p_types = array();  /// create array for post types
if(count($posttypes) > 0) { // check if size of array is greater than one
foreach($posttypes as $ptypesval) {
array_push($p_types, $ptypesval);
}
}else{
$p_types = ''; // if size of array is 0 than store it as blank
}

$cr_content_show = $_POST['cr_content_show'];

// update value

update_option('cr_contentstype',$p_types);
update_option('cr_content_show',$cr_content_show);

$msg="Setting Updated";
}

$ptyps = get_option('cr_contentstype');
$contentshow = get_option('cr_content_show');
?>
<form action="" method="post" name="cr_option" class="form-table">
<h2><?php _e('Setting'); ?></h2>
<p><?php if($msg != "") { echo '<div class="updated fade">'.$msg.'</div>'; } ?></p>
<table cellpadding="5" cellspacing="5" class="widefat">

<tr><td><?php _e('Restrict Content Type'); ?></td>
<td>
<?php
$post_types = get_post_types( '', 'names' ); 
foreach ( $post_types as $post_type ) {
if($post_type=='attachment' || $post_type=='revision' || $post_type=='nav_menu_item' )
continue;
?>
<input type="checkbox" name="cr_restrict_type[]" value="<?php echo $post_type; ?>" <?php if($ptyps!='' && in_array($post_type,$ptyps)) { ?> checked <?php } ?>>&nbsp;<?php echo ucfirst($post_type); ?>&nbsp;&nbsp;
<?php } ?>
</td></tr>
<tr><td><?php _e('Content To Show'); ?></td><td><textarea rows="5" cols="30" name="cr_content_show" style="width:300px;"><?php echo $contentshow; ?></textarea></td></tr>
<tr><td></td><td><?php echo submit_button(); ?></td></tr>
</table>
</form>
