<?php
/*
Plugin Name: Display Post Meta
Description: This plugin allows you to display the meta data for a post. Just click the DPM link in the toolbar to show meta info.
Version: 1.4
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/

/*
Credit for taxonomies part goes here http://stackoverflow.com/questions/14956624/show-all-taxonomies-for-a-specific-post-type
*/

class DisplayPostMeta {

	public function __construct() {
		add_action( 'wp_footer', array( $this, 'activate' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_style' ) );
	}

	// Get all the custom field data
	function get_post_meta_all( $post_id ) {
		global $wpdb;
		$data = array();
		$wpdb->query( "
          SELECT `meta_key`, `meta_value`
          FROM $wpdb->postmeta
          WHERE `post_id` = $post_id
      " );
		foreach ( $wpdb->last_result as $k => $v ) {
			$data[ $v->meta_key ] = $v->meta_value;
		};

		return $data;
	}

	// Register stylesheet
	function register_style() {
		$show_meta = isset( $_GET['show_meta'] ) ? true : false;
		wp_register_style( 'DPMstyle', plugins_url( 'style.css', __FILE__ ), array(), '1.4' );
		if ( $show_meta === true ) {
			wp_enqueue_style( 'DPMstyle' );
		}
	}

	// Display custom field data
	public function custom_fields() {
		$id   = get_the_ID();
		$meta = $this->get_post_meta_all( $id );
		echo '<span class="meta-tab">Custom Fields</span>';
		if ( $meta ) {
			echo '<ul>';
			foreach ( $meta as $key => $value ) {
				echo '<li><strong>' . esc_html( $key ) . '</strong>: ';
				echo wp_kses_post( $value ) . '</li>';
			}
			echo '</ul>';
		} else {
			echo 'This post has no custom field data.';
		}
	}

	// Get and display taxonomies
	public function taxonomies() {
		$post_type = get_post_type();
		$id        = get_the_ID();
		foreach ( get_object_taxonomies( $post_type ) as $taxonomy ) {
			$terms_list = get_the_term_list( $id, $taxonomy, '<ul class="entry-taxonomies"><ul class="tax-terms"><li>', '' . __( '', '' ) . '</li><li>', '</li></ul></div>' );
			if ( $terms_list ) {
				?>
				<div class="tax-tab">
				<span class="tax-taxonomy"><?php echo esc_html( $taxonomy ); ?></span>
				<?php echo wp_kses_post( $terms_list );
			}
		}
	}

	// Get other pertinent data
	public function other() {
		echo '<span class="meta-tab">Other</span>';
		echo '<ul>';
		echo '<li><strong>ID: </strong>' . get_the_id() . '</li>';
		echo '<li><strong>Author: </strong>' . get_the_author() . '</li>';
		echo '<li><strong>Published: </strong>' . get_the_date() . '</li>';
		echo '<li><strong>Last modified: </strong>' . get_the_modified_date() . '</li>';
		echo '<li><strong>Post status: </strong>' . get_post_status() . '</li>';
		echo '<li><strong>Template: </strong>' . basename( get_page_template() ) . '</li>';
		echo '</ul>';
	}

	// Actually display the stuff
	public function activate() {
		$show_meta = isset( $_GET['show_meta'] ) ? true : false;
		if ( $show_meta === true && is_user_logged_in() ) {
			echo '<div class="show-meta">';
			echo '<span class="dpm-close"><a href="' . remove_query_arg( 'show_meta' ) . '">X</a></span>';
			$this->custom_fields();
			$this->taxonomies();
			$this->other();
			echo '</div>';
		}
	}

}

$dpm = new DisplayPostMeta;
include( 'admin-bar.php' );