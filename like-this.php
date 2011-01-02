<?php
/*
Plugin Name: Like This
Plugin URI: http://new2wp.com/like-this/
Description: This plugin allows your visitors to like your posts with a simple click.
Version: 1.0
Author: Jared Williams
Author URI: http://new2wp.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

#### LOAD TRANSLATIONS ####
load_plugin_textdomain( 'like-this', 'wp-content/plugins/like-this/lang/', 'like-this/lang/' );
####


#### INSTALL PROCESS ####
$lt_dbVersion = "1.0";

function setOptionsLT() {
	global $wpdb;
	global $lt_dbVersion;
	
	$table_name = $wpdb->prefix . "likethis_votes";
	
	if( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
		$sql = "CREATE TABLE " . $table_name . " (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			time TIMESTAMP NOT NULL,
			post_id BIGINT(20) NOT NULL,
			ip VARCHAR(15) NOT NULL,
			UNIQUE KEY id (id)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( "lt_dbVersion", $lt_dbVersion );
	}
	
	add_option( 'lt_jquery', '1', '', 'yes' );
	add_option( 'lt_onPage', '1', '', 'yes' );
	add_option( 'lt_textOrImage', 'image', '', 'yes' );
	add_option( 'lt_text', 'Like This', '', 'yes' );
}

register_activation_hook( __FILE__, 'setOptionsLT' );

function unsetOptionsLT() {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "likethis_votes" );

	delete_option( 'lt_jquery' );
	delete_option( 'lt_onPage' );
	delete_option( 'lt_textOrImage' );
	delete_option( 'lt_text' );
	delete_option( 'lt_most_liked_posts' );
	delete_option( 'lt_dbVersion' );
}
register_uninstall_hook( __FILE__, 'unsetOptionsLT' );
####


#### ADMIN OPTIONS ####
function LikeThisAdminMenu() {
	add_options_page( __( 'Like This' ), __( 'Like This' ), 'manage_options', 'LikeThisAdminMenu', 'LikeThisAdminContent' );
}

function LikeThisAdminRegisterSettings() { // whitelist options
	register_setting( 'lt_options', 'lt_jquery' );
	register_setting( 'lt_options', 'lt_onPage' );
	register_setting( 'lt_options', 'lt_textOrImage' );
	register_setting( 'lt_options', 'lt_text' );
}

function LikeThisAdminContent() { ?>
	<div class="wrap">
		<h2><?php _e( '"Like This" Options' ); ?></h2>
		<br class="clear" />
				
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<div id="likethisoptions" class="postbox">
			<h3><?php _e( 'Configuration' ); ?></h3>
				<div class="inside">
				<form method="post" action="options.php">
				<?php settings_fields('lt_options'); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="lt_jquery"><?php _e('jQuery framework', 'like-this'); ?></label></th>
						<td>
							<select name="lt_jquery" id="lt_jquery">
								<?php echo get_option('lt_jquery') == '1' ? '<option value="1" selected="selected">'.__('Enabled', 'like-this').'</option><option value="0">'.__('Disabled', 'like-this').'</option>' : '<option value="1">'.__('Enabled', 'like-this').'</option><option value="0" selected="selected">'.__('Disabled', 'like-this').'</option>'; ?>
							</select>
							<span class="description"><?php _e('Disable it if you already have the jQuery framework enabled in your theme.', 'like-this'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><legend><?php _e('Image or text?', 'like-this'); ?></legend></th>
						<td>
							<label for="lt_textOrImage" style="padding:3px 20px 3px 0; margin-right:20px; background: url(<?php echo WP_PLUGIN_URL.'/like-this/css/add.png'; ?>) no-repeat right center;">
							<?php echo get_option('lt_textOrImage') == 'image' ? '<input type="radio" name="lt_textOrImage" id="lt_textOrImage" value="image" checked="checked">' : '<input type="radio" name="lt_textOrImage" id="lt_textOrImage" value="image">'; ?>
							</label>
							<label for="lt_text">
							<?php echo get_option('lt_textOrImage') == 'text' ? '<input type="radio" name="lt_textOrImage" id="lt_textOrImage" value="text" checked="checked">' : '<input type="radio" name="lt_textOrImage" id="lt_textOrImage" value="text">'; ?>
							<input type="text" name="lt_text" id="lt_text" value="<?php echo get_option('lt_text'); ?>" />
							</label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><legend><?php _e('Automatic display', 'like-this'); ?></legend></th>
						<td>
							<label for="lt_onPage">
							<?php echo get_option('lt_onPage') == '1' ? '<input type="checkbox" name="lt_onPage" id="lt_onPage" value="1" checked="checked">' : '<input type="checkbox" name="lt_onPage" id="lt_onPage" value="1">'; ?>
							<?php _e('<strong>On all posts</strong> (home, archives, search) at the bottom of the post', 'like-this'); ?>
							</label>
							<p class="description">
								<?php _e( 'To manually display the voting button where you choose, simply edit the theme template files according to where you want it to show by adding this code to your theme.', 'votethis'); ?>
							</p>
							<span class="embedcode"><?php _e( 'Embed code:' ); ?> <input type='text' value='&lt;?php if(function_exists('getVoteThis')) { getVoteThis('get'); } ?&gt;' onclick='this.focus(); this.select();' class="embedcode" /></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options', 'like-this'); ?>" /></th>
						<td></td>
					</tr>
				</table>
				</form>
				</div>
			</div>
		</div>
		
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<div id="likethisoptions" class="postbox">
			<h3><?php _e('You like this plugin?'); ?></h3>
				<div class="inside">
					<form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="business" value="jaredwilli@gmail.com">
					<input type="hidden" name="item_name" value="Wordpress plugin">
					<input type="image" src="http://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit">
					</form>
				</div>
			</div>
		</div>
	</div>

<?php
}
####


#### WIDGET ####
function lt_most_liked_posts( $numberOf, $before, $after, $show_count ) {
	global $wpdb;

    $request = "SELECT ID, post_title, meta_value FROM $wpdb->posts, $wpdb->postmeta";
    $request .= " WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id";
    $request .= " AND post_status='publish' AND meta_key='_liked'";
    $request .= " ORDER BY $wpdb->postmeta.meta_value+0 DESC LIMIT $numberOf";
    $posts = $wpdb->get_results( $request );

    foreach ( $posts as $post ) {
    	$post_title = stripslashes($post->post_title);
    	$permalink = get_permalink($post->ID);
    	$post_count = $post->meta_value;
    	
    	echo $before . '<a href="' . $permalink . '" title="' . $post_title . '" rel="nofollow">' . $post_title . '</a>';
		echo $show_count == '1' ? ' (' . $post_count . ')' : '';
		echo $after;
    }
}

function add_widget_most_liked_posts() {
	function widget_most_liked_posts( $args ) {
		extract( $args );
		$options = get_option( "lt_most_liked_posts" );
		if (!is_array( $options )) {
			$options = array(
				'title' => 'Most liked posts',
				'number' => '5',
				'show_count' => '0'
			);
		}
		$title = $options['title'];
		$numberOf = $options['number'];
		$show_count = $options['show_count'];
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul class="mostlikedposts">';

		lt_most_liked_posts( $numberOf, '<li>', '</li>', $show_count );
		
		echo '</ul>';
		echo $after_widget;
	}	
	
	function options_widget_most_liked_posts() {
		$options = get_option("lt_most_liked_posts");
		
		if (!is_array( $options )) {
			$options = array(
				'title' => 'Most liked posts',
				'number' => '5',
				'show_count' => '0'
			);
		}
		
		if ($_POST['mlp-submit']) {
			$options['title'] = htmlspecialchars( $_POST['mlp-title'] );
			$options['number'] = htmlspecialchars( $_POST['mlp-number'] );
			$options['show_count'] = $_POST['mlp-show-count'];
			if ( $options['number'] > 15 ) { $options['number'] = 15; }
			
			update_option( "lt_most_liked_posts", $options );
		}
		?>
		<p><label for="mlp-title"><?php _e('Title:', 'like-this'); ?><br />
		<input class="widefat" type="text" id="mlp-title" name="mlp-title" value="<?php echo $options['title'];?>" /></label></p>
		
		<p><label for="mlp-number"><?php _e('Number of posts to show:', 'like-this'); ?><br />
		<input type="text" id="mlp-number" name="mlp-number" style="width: 25px;" value="<?php echo $options['number'];?>" /> <small>(max. 15)</small></label></p>
		
		<p><label for="mlp-show-count"><input type="checkbox" id="mlp-show-count" name="mlp-show-count" value="1"<?php if($options['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php _e('Show post count', 'like-this'); ?></label></p>
		
		<input type="hidden" id="mlp-submit" name="mlp-submit" value="1" />
	<?php
	}

	wp_register_sidebar_widget( __( 'Most liked posts' ), 'widget_most_liked_posts', '');
	wp_register_widget_control( __( 'Most liked posts' ), 'options_widget_most_liked_posts', '' );

}
add_action( 'init', 'add_widget_most_liked_posts' );
####


#### FRONT-END VIEW ####
function getLikeThis( $arg ) {
	global $wpdb;
	$post_ID = get_the_ID();
	$ip = $_SERVER['REMOTE_ADDR'];
	
   	$liked = get_post_meta( $post_ID, '_liked', true ) != '' ? get_post_meta( $post_ID, '_liked', true ) : '0';
	
	$voteStatusByIp = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "likethis_votes WHERE post_id = '$post_ID' AND ip = '$ip'");
		
    if ( !isset( $_COOKIE['liked-' . $post_ID ] ) && $voteStatusByIp == 0 ) {

    	if (get_option('lt_textOrImage') == 'image') {
    		$counter = '<a onclick="likeThis(' . $post_ID . ');" class="image">' . $liked . '</a>';
    	} else {
    		$counter = '<a onclick="likeThis(' . $post_ID . ');">' . get_option('lt_text') . '</a>'; // $liked
    	}
	
    } else {
    	 $counter = ''; // $liked; commented out to hide number of votes
    }
    
	//var_dump($voteStatusByIp); 
	
    $LikeThis = '<div id="LikeThis-' . $post_ID . '" class="LikeThis">';
		if ( $voteStatusByIp == '1' ) {
			$LikeThis .= '<span class="inactive">' . $counter . '</span>';
		} elseif( $voteStatusByIp == '0' ) {
	    	$LikeThis .= '<span class="counter">' . $counter . '</span>';
		}

    $LikeThis .= '</div>';
    
    if ($arg == 'put') {
	    return $LikeThis;
    }
    else {
	echo '<h3>Votes: ' . $liked . '</h3>';
	/*
	if (is_page('finalists')) {
	    	echo $LikeThis;
	} else {
		echo '<h3>Votes: ' . $liked . '</h3>';
	}
	*/
    }
}

if ( get_option( 'lt_onPage' ) == '1' ) {
	function putLikeThis( $content ) {
		if( !is_feed() && !is_page() ) {
			$content .= getLikeThis( 'put' );
		}
	    return $content;
	}
	add_filter( 'the_content', 'putLikeThis' );
}

function enqueueScripts() {
	if ( get_option( 'lt_jquery') == '1' ) {
	    wp_enqueue_script( 'LikeThis', WP_PLUGIN_URL.'/like-this/js/like-this.js', array( 'jquery' ));
	} else {
	    wp_enqueue_script( 'LikeThis', WP_PLUGIN_URL.'/like-this/js/like-this.js' );
	}
}

function addFooterLinks() {
	echo '<link rel="stylesheet" type="text/css" href="' . WP_PLUGIN_URL . '/like-this/css/like-this.css" media="screen" />'."\n";
	echo '<script type="text/javascript">var blogUrl = \'' . site_url() . '\'</script>' . "\n";
}

add_action( 'admin_menu', 'LikeThisAdminMenu' );
add_action( 'admin_init', 'LikeThisAdminRegisterSettings' );
add_action( 'init', 'enqueueScripts' );
add_action( 'wp_footer', 'addFooterLinks' );
?>