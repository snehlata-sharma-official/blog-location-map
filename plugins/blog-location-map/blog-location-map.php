<?php
/**
 *
 * Adds location data to blogs.
 *
 * @since             1.0.0
 * @package           blog-location-map
 *
 * @wordpress-plugin
 * Plugin Name: Blog Location Map
 * Description: Adds location data to blog posts and displays them on a map using Leaflet.
 * Version: 1.0.0
 * Author: Snehlata Sharma
 */

/**
 *  Register a meta box for the location fields.
 */
function blog_map_add_location_metabox() {
	add_meta_box(
		'blog_location',
		'Blog Location',
		'blog_map_location_callback',
		'post',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'blog_map_add_location_metabox' );

/**
 * Display meta box fields (latitude, longitude).
 *
 * @param object $post Post Object.
 */
function blog_map_location_callback( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'blog_map_nonce' );
	$lat = get_post_meta( $post->ID, '_blog_map_latitude', true );
	$lng = get_post_meta( $post->ID, '_blog_map_longitude', true );
	?>
	<p>
		<label for="blog_map_latitude">Latitude:</label>
		<input type="text" id="blog_map_latitude" name="blog_map_latitude" value="<?php echo esc_attr( $lat ); ?>" />
	</p>
	<p>
		<label for="blog_map_longitude">Longitude:</label>
		<input type="text" id="blog_map_longitude" name="blog_map_longitude" value="<?php echo esc_attr( $lng ); ?>" />
	</p>
	<?php
}

/**
 * Save the location metadata.
 *
 * @param object $post_id Get the post Id.
 */
function blog_map_save_location_metadata( $post_id ) {
	if ( ! isset( $_POST['blog_map_nonce'] ) || ! wp_verify_nonce( $_POST['blog_map_nonce'], basename( __FILE__ ) ) ) {
		return;
	}
	$lat = sanitize_text_field( wp_unslash( $_POST['blog_map_latitude'] ) );
	$lng = sanitize_text_field( wp_unslash( $_POST['blog_map_longitude'] ) );
	update_post_meta( $post_id, '_blog_map_latitude', $lat );
	update_post_meta( $post_id, '_blog_map_longitude', $lng );
}
add_action( 'save_post', 'blog_map_save_location_metadata' );

/**
 * Enqueue Leaflet CSS and JS.
 */
function blog_map_enqueue_leaflet_assets() {
	wp_enqueue_style( 'leaflet-css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css' );
	wp_enqueue_script( 'leaflet-js', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js', array(), '1.0.0', true );
	wp_enqueue_script( 'blog-leaflet-script', plugin_dir_url( __FILE__ ) . 'assets/js/custom-leaflet-script.js', array( 'leaflet-js' ), '1.0.0', true );
	wp_localize_script( 'blog-leaflet-script', 'blog_map_map_data', blog_map_get_map_data() );
}
add_action( 'wp_enqueue_scripts', 'blog_map_enqueue_leaflet_assets' );

/**
 * Get map data (posts with latitude and longitude).
 */
function blog_map_get_map_data() {
	$args = array(
		'post_type' => 'post',
		'meta_query' => array(
			array(
				'key' => '_blog_map_latitude',
				'compare' => 'EXISTS',
			),
			array(
				'key' => '_blog_map_longitude',
				'compare' => 'EXISTS',
			),
		),
	);
	$query = new WP_Query( $args );
	$posts = array();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$lat = get_post_meta( get_the_ID(), '_blog_map_latitude', true );
			$lng = get_post_meta( get_the_ID(), '_blog_map_longitude', true );
			$posts[] = array(
				'title' => get_the_title(),
				'link' => get_permalink(),
				'lat' => $lat,
				'lng' => $lng,
			);
		}
	}
	wp_reset_postdata();
	return $posts;
}


/**
 * Function to output the map HTML.
 */
function blog_map_display_map_shortcode() {
	// Return the HTML for the map container.
	return '<div id="map" style="height: 500px;"></div>';
}

/**
 * Register the shortcode.
 */
function blog_map_register_shortcodes() {
	add_shortcode( 'blog_map_display_map', 'blog_map_display_map_shortcode' );
}

/**
 * Hook the shortcode registration into the 'init' action.
*/
add_action( 'init', 'blog_map_register_shortcodes' );
