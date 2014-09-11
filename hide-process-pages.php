<?php
/*
Plugin Name: Hide Process Pages
Plugin URI: http://stomptheweb.co.uk
Description: Hide process pages from users that are crucial to a process
Version: 1.0.0
Author: Steven Jones
Author URI: http://stomptheweb.co.uk/
License: GPL2
*/

// Hide pages from non-Administrators
function hpp_hide_pages_admin( $query ) {

	if ('administrator' == hpp_get_user_role()) {
		return;
	}

	global $typenow;
	
	if ( 'page' != $typenow) {
		return;
	}

	$current_selection = get_option('hpp_pages');

    if ( is_admin() && $query->is_main_query() ) {
        $query->set( 'post__not_in', $current_selection );
    }
}
add_action( 'pre_get_posts', 'hpp_hide_pages_admin' );

// Add a submenu item labelled 'Process Pages' the pages menu item.
function hpp_add_submenu_page() {
	
	global $hide_process_pages;
	$hide_process_pages = add_submenu_page( 'edit.php?post_type=page', 'Hide Process Pages', 'Process Pages', 'manage_options', 'process-pages', 'hpp_manage_pages' );

}
add_action('admin_menu', 'hpp_add_submenu_page');

// Admin page manage the process pages
function hpp_manage_pages() { ?>

	<script type="text/javascript">
 		jQuery(document).ready(function() { jQuery("#hide-pages").select2(); });
	</script>

	<style>
		.select2-container {
			width: 50%;
			min-width: 320px;
		}
		.select2-results {
			width: 100%;
		}
	</style>

	<h2>Hide Process Pages</h2>

	<?php if ( isset( $_POST['hpp_update_pages'] ) && $_POST['hpp_update_pages'] ) { ?>

		<div id="message" class="updated">
			<p>Selection Updated.</p>
		</div>

		<br />

	<?php } ?>

	<p>Select the pages that should be hidden users (Non-Administrators). They will be hidden from the pages section.</p>

	<form method="POST">

		<?php 

		$pages = get_pages();
		$current_selection = get_option('hpp_pages');

		?>
	  
		<select name="process_pages[]" id="hide-pages" multiple>
			<?php foreach ( $pages as $page ) { ?>
				<option value="<?php echo $page->ID; ?>" <?php if(in_array($page->ID, $current_selection)) { echo 'selected'; } ?>>
					<?php echo $page->post_title; ?>
				</option>
			<?php } ?>
		</select>
		
		<p class="submit">
			<input class="button-primary" type="submit" name="hpp_update_pages" value="Update Selection"/>
		</p>

	</form>


<?php }

function hpp_update_pages() {

	$process_pages = array();

	if (isset($_POST['hpp_update_pages'])) {
	
		$process_pages = $_POST['process_pages'];

		update_option('hpp_pages', $process_pages);

	}

}
add_action('admin_init', 'hpp_update_pages');

function hpp_enqueue_select2($hook) {
	
	global $hide_process_pages;
	
	if ($hook != $hide_process_pages) {
		return;
	}

	wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2/select2.min.js', array( 'jquery' ));
	wp_register_style( 'select2',  plugin_dir_url( __FILE__ ) . 'js/select2/select2.css',    array(          ));
	
	wp_enqueue_script( 'select2' );
	wp_enqueue_style( 'select2' );
	
}
add_action('admin_enqueue_scripts', 'hpp_enqueue_select2');

function hpp_get_user_role() {
	global $wp_roles;
	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$role = array_shift($roles);
	return $role;
}