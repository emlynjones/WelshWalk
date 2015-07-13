<?php
/*
Plugin Name: Welsh Walk Plugin
Plugin URI: http://www.cambrianweb.com
Description: Plugin designed to allow users to submit and share walks and trails around Wales.
Version: 0.0.2

Author: Emlyn Jones
Author URI: http://www.emlynjones.co.uk
License: GPL v2
*/

/*
Copyright 2014 Emlyn Jones (email: emlyn@cambrianweb.com)

This program is free software; you can distribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*/

/* This function runs when the plugin is activated */
/*
register_activation_hook(__FILE__, 'wwalk_activate');
function wwalk_activate(){

	Save the default options...

	$wwalk_options = array(
		'YesorNo' => 'Yes'
	);
	update_option ('wwalk_option', $wwalk_options);

}
*/
add_action('init', 'wwalk_init');
function wwalk_init() {
	//Register a new custom post type
	$labels = array(
		'name' => __('Walks', 'wwalk-plugin'),
		'singular_name' => __('Walk', 'wwalk-plugin'),
		'add_new' => __('Add New', 'wwalk-plugin'),
		'add_new_item' => __('Add New Walk', 'wwalk-plugin'),
		'edit_item' => __('Edit Walk', 'wwalk-plugin'),
		'new_item' => __('New Walk', 'wwalk-plugin'),
		'all_items' => __('All Walks', 'wwalk-plugin'),
		'view_item' => __('View Walks', 'wwalk-plugin'),
		'search_items' => __('Search Walks', 'wwalk-plugin'),
		'not_found' => __('No walks found...', 'wwalk-plugin'),
		'not_found_in_trash' => __('No walks found in trash', 'wwalk-plugin'),
		'menu_name' => __('Walks', 'wwalk-plugin')
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'shou_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'has_archive' => true,
		'hierarchical' => true,
		'menu_positions' => true,
		'supports' => array ('title', 'editor', 'thumbnail',)
	);

	//Register the post type
	register_post_type('welsh-walks', $args);


	//Create meta boxes
	add_action('add_meta_boxes', 'wwalk_register_meta_boxes');



	//Register metaboxes for Walks information
	function wwalk_register_meta_boxes(){
		add_meta_box('walk_meta', __('Walk Information', 'wwalk-plugin'), 'wwalk_meta_box', 'welsh-walks', 'normal', 'high');
	};

	//Show the Metabox
	function wwalk_meta_box($post){
		//retrieve the meta box values


		//display metabox form

		//Display a Map using Google Maps
		echo '<script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB1uzCE_C_1BiAOdQxty2iYBPH0m7Biz7w">
    </script>
    <script type="text/javascript">
      function initialize() {
        var mapOptions = {
          center: { lat: 52.498, lng: -3.642},
          zoom: 8
        };
        var map = new google.maps.Map(document.getElementById("map-canvas"),
            mapOptions);
				var flightPlanCoordinates = [
	    new google.maps.LatLng(37.772323, -122.214897),
	    new google.maps.LatLng(21.291982, -157.821856),
	    new google.maps.LatLng(-18.142599, 178.431),
	    new google.maps.LatLng(-27.46758, 153.027892)
	  ];
	  var flightPath = new google.maps.Polyline({
	    path: flightPlanCoordinates,
	    geodesic: true,
	    strokeColor: "#FF0000",
	    strokeOpacity: 1.0,
	    strokeWeight: 2,
			editable:true
	  });

	  flightPath.setMap(map);
      }

      google.maps.event.addDomListener(window, "load", initialize);

    </script>

  <body>
<div id="map-canvas" style ="height:500px; width:100%;"></div>';



	};

	//Save the Metabox.
	//add_action('save_post', 'wwalk_save_meta_box')

	function wwalk_save_meta_box($post_id){
		//save the data to tables

	}
}

/*
Creating the custom table - see guide here: http://code.tutsplus.com/tutorials/custom-database-tables-creating-the-table--wp-28124
*/

//Store the name of the custom table in global WP $wpdb - DB prefix & wwalk_spatial_table
add_action( 'init', 'wwalk_register_spatial_walks_table', 1 );
add_action( 'switch_blog', 'wwalk_register_spatial_walks_table' );

function wwalk_register_spatial_walks_table() {
    global $wpdb;
    $wpdb->wwalk_spatial_table = "{$wpdb->prefix}wwalk_spatial_table";
}

/*
Fields should include only those which are spatial, and that links to wp_posts table. All could be stored as "GEOMETRY"
	* geometry_class - store if it is photo/walk/rest/parking etc. -
	* walk_line
	* walk_point
	* walk_polygon
*
*/
// Create tables on plugin activation
register_activation_hook( __FILE__, 'wwalk_create_spatial_table' );


function wwalk_create_spatial_table() {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	global $wpdb;
	global $charset_collate;

	// Call this manually as we may have missed the init hook during activating the plugin
	wwalk_register_spatial_walks_table();
	$spatial_table_name = $wpdb->wwalk_spatial_table;
	$sql_create_table = "CREATE TABLE ".$spatial_table_name." (
				SPATIAL_TABLE_ID INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				POST_ID INT(20) UNSIGNED NOT NULL DEFAULT '0',
				GEOMETRY_CLASS VARCHAR(15) NOT NULL
				GEOMETRY_SHAPE GEOMETRY NULL,
				PRIMARY KEY  (SPATIAL_TABLE_ID),
				KEY (GEOMETRY_CLASS)
			)$charset_collate; ";

dbDelta( $sql_create_table );
//wp_die();
}
