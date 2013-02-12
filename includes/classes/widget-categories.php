<?php
/**
 * Categories Widget
 *
 * Inspired by default WordPress categories widget but adds support for selecting any exposed taxonomy.
 */

class CTC_Widget_Categories extends CTC_Widget {

	/**
	 * Register widget with WordPress
	 */

	function __construct() {

		parent::__construct(
			'ctc-categories',
			_x( 'CT Categories', 'widget', 'church-theme' ),
			array(
				'description' => __( 'Shows categories of various types', 'church-theme' )
			)			
		);

		// Redirect Dropdown URL
		add_action( 'template_redirect', array( &$this, 'ctc_redirect_taxonomy' ) ); // used with Categories widget dropdown redirects

	}

	/**
	 * Field configuration
	 *
	 * This is used by CTC_Widget class for automatic field output, filtering, sanitization and saving.
	 */
	 
	function ctc_fields() { // prefix in case WP core adds method with same name

		// Fields
		$fields = array(

			// Example
			/*
			'field_id' => array(
				'name'				=> __( 'Field Name', 'ccm' ),
				'after_name'		=> '', // (Optional), (Required), etc.
				'desc'				=> __( 'This is the description below the field.', 'ccm' ),
				'type'				=> 'text', // text, textarea, checkbox, radio, select, number, url, image
				'checkbox_label'	=> '', //show text after checkbox
				'radio_inline'		=> false, // show radio inputs inline or on top of each other
				'number_min'		=> '', // lowest possible value for number type
				'number_max'		=> '', // highest possible value for number type
				'options'			=> array(), // array of keys/values for radio or select
				'default'			=> '', // value to pre-populate option with (before first save or on reset)
				'no_empty'			=> false, // if user empties value, force default to be saved instead
				'allow_html'		=> false, // allow HTML to be used in the value (text, textarea)
				'attributes'		=> array(), // attributes to add to input element
				'class'				=> '', // class(es) to add to input
				'field_attributes'	=> array(), // attr => value array for field container
				'field_class'		=> '', // class(es) to add to field container
				'custom_sanitize'	=> '', // function to do additional sanitization (or array( &$this, 'method' ))
				'custom_field'		=> '', // function for custom display of field input (or array( &$this, 'method' ))
				'taxonomies'		=> array(), // hide field if taxonomies are not supported
			);
			*/

			// Title
			'title' => array(
				'name'				=> _x( 'Title', 'categories widget', 'church-theme' ),
				'after_name'		=> '', // (Optional), (Required), etc.
				'desc'				=> '',
				'type'				=> 'text', // text, textarea, checkbox, radio, select, number, url, image
				'checkbox_label'	=> '', //show text after checkbox
				'radio_inline'		=> false, // show radio inputs inline or on top of each other
				'number_min'		=> '', // lowest possible value for number type
				'number_max'		=> '', // highest possible value for number type
				'options'			=> array(), // array of keys/values for radio or select
				'default'			=> '', // value to pre-populate option with (before first save or on reset)
				'no_empty'			=> false, // if user empties value, force default to be saved instead
				'allow_html'		=> false, // allow HTML to be used in the value (text, textarea)
				'attributes'		=> array(), // attributes to add to input element
				'class'				=> '', // class(es) to add to input
				'field_attributes'	=> array(), // attr => value array for field container
				'field_class'		=> '', // class(es) to add to field container
				'custom_sanitize'	=> '', // function to do additional sanitization (or array( &$this, 'method' ))
				'custom_field'		=> '', // function for custom display of field input (or array( &$this, 'method' ))
				'taxonomies'		=> array(), // hide field if taxonomies are not supported
			),
			
			// Type
			'taxonomy' => array(
				'name'				=> _x( 'Type', 'categories widget', 'church-theme' ),
				'after_name'		=> '', // (Optional), (Required), etc.
				'desc'				=> '',
				'type'				=> 'select', // text, textarea, checkbox, radio, select, number, url, image
				'checkbox_label'	=> '', //show text after checkbox
				'radio_inline'		=> false, // show radio inputs inline or on top of each other
				'number_min'		=> '', // lowest possible value for number type
				'number_max'		=> '', // highest possible value for number type
				'options'			=> $this->ctc_taxonomy_options(), // array of keys/values for radio or select
				'default'			=> 'category', // value to pre-populate option with (before first save or on reset)
				'no_empty'			=> true, // if user empties value, force default to be saved instead
				'allow_html'		=> false, // allow HTML to be used in the value (text, textarea)
				'attributes'		=> array(), // attributes to add to input element
				'class'				=> '', // class(es) to add to input
				'field_attributes'	=> array(), // attr => value array for field container
				'field_class'		=> '', // class(es) to add to field container
				'custom_sanitize'	=> '', // function to do additional sanitization (or array( &$this, 'method' ))
				'custom_field'		=> '', // function for custom display of field input (or array( &$this, 'method' ))
				'taxonomies'		=> array(), // hide field if taxonomies are not supported
			),
			
			// Order By
			'orderby' => array(
				'name'				=> _x( 'Order By', 'categories widget', 'church-theme' ),
				'after_name'		=> '', // (Optional), (Required), etc.
				'desc'				=> '',
				'type'				=> 'select', // text, textarea, checkbox, radio, select, number, url, image
				'checkbox_label'	=> '', //show text after checkbox
				'radio_inline'		=> false, // show radio inputs inline or on top of each other
				'number_min'		=> '', // lowest possible value for number type
				'number_max'		=> '', // highest possible value for number type
				'options'			=> array( // array of keys/values for radio or select
					'title'			=> _x( 'Title', 'categories widget order by', 'church-theme' ),
					'publish_date'	=> _x( 'Date', 'categories widget order by', 'church-theme' ),
					'count'			=> _x( 'Post Count', 'categories widget order by', 'church-theme' ),
				),
				'default'			=> 'title', // value to pre-populate option with (before first save or on reset)
				'no_empty'			=> true, // if user empties value, force default to be saved instead
				'allow_html'		=> false, // allow HTML to be used in the value (text, textarea)
				'attributes'		=> array(), // attributes to add to input element
				'class'				=> '', // class(es) to add to input
				'field_attributes'	=> array(), // attr => value array for field container
				'field_class'		=> 'ctc-widget-no-bottom-margin', // class(es) to add to field container
				'custom_sanitize'	=> '', // function to do additional sanitization (or array( &$this, 'method' ))
				'custom_field'		=> '', // function for custom display of field input (or array( &$this, 'method' ))
				'taxonomies'		=> array(), // hide field if taxonomies are not supported
			),
			
			// Order
			'order' => array(
				'name'				=> '',
				'after_name'		=> '', // (Optional), (Required), etc.
				'desc'				=> '',
				'type'				=> 'radio', // text, textarea, checkbox, radio, select, number, url, image
				'checkbox_label'	=> '', // show text after checkbox
				'radio_inline'		=> true, // show radio inputs inline or on top of each other
				'number_min'		=> '', // lowest possible value for number type
				'number_max'		=> '', // highest possible value for number type
				'options'			=> array( // array of keys/values for radio or select
					'asc'	=> _x( 'Low to High', 'categories widget order', 'church-theme' ),
					'desc'	=> _x( 'High to Low', 'categories widget order', 'church-theme' ),
				),
				'default'			=> 'asc', // value to pre-populate option with (before first save or on reset)
				'no_empty'			=> true, // if user empties value, force default to be saved instead
				'allow_html'		=> false, // allow HTML to be used in the value (text, textarea)
				'attributes'		=> array(), // attributes to add to input element
				'class'				=> '', // class(es) to add to input
				'field_attributes'	=> array(), // attr => value array for field container
				'field_class'		=> '', // class(es) to add to field container
				'custom_sanitize'	=> '', // function to do additional sanitization (or array( &$this, 'method' ))
				'custom_field'		=> '', // function for custom display of field input (or array( &$this, 'method' ))
				'taxonomies'		=> array(), // hide field if taxonomies are not supported
			),
			
			// Limit
			'limit' => array(
				'name'				=> _x( 'Limit', 'categories widget', 'church-theme' ),
				'after_name'		=> '', // (Optional), (Required), etc.
				'desc'				=> _x( 'Set to 0 for unlimited.', 'categories widget', 'church-theme' ),
				'type'				=> 'number', // text, textarea, checkbox, radio, select, number, url, image
				'checkbox_label'	=> '', //show text after checkbox
				'radio_inline'		=> false, // show radio inputs inline or on top of each other
				'number_min'		=> '0', // lowest possible value for number type
				'number_max'		=> '', // highest possible value for number type
				'options'			=> array(), // array of keys/values for radio or select
				'default'			=> '0', // value to pre-populate option with (before first save or on reset)
				'no_empty'			=> false, // if user empties value, force default to be saved instead
				'allow_html'		=> false, // allow HTML to be used in the value (text, textarea)
				'attributes'		=> array(), // attributes to add to input element
				'class'				=> '', // class(es) to add to input
				'field_attributes'	=> array(), // attr => value array for field container
				'field_class'		=> '', // class(es) to add to field container
				'custom_sanitize'	=> '', // function to do additional sanitization (or array( &$this, 'method' ))
				'custom_field'		=> '', // function for custom display of field input (or array( &$this, 'method' ))
				'taxonomies'		=> array(), // hide field if taxonomies are not supported
			),
			
			// Count
			'show_count' => array(
				'name'				=> '',
				'after_name'		=> '', // (Optional), (Required), etc.
				'desc'				=> '',
				'type'				=> 'checkbox', // text, textarea, checkbox, radio, select, number, url, image
				'radio_inline'		=> false, // show radio inputs inline or on top of each other
				'number_min'		=> '', // lowest possible value for number type
				'number_max'		=> '', // highest possible value for number type
				'checkbox_label'	=> _x( 'Show counts', 'categories widget', 'church-theme' ), //show text after checkbox
				'options'			=> array(), // array of keys/values for radio or select
				'default'			=> true, // value to pre-populate option with (before first save or on reset)
				'no_empty'			=> false, // if user empties value, force default to be saved instead
				'allow_html'		=> false, // allow HTML to be used in the value (text, textarea)
				'attributes'		=> array(), // attributes to add to input element
				'class'				=> '', // class(es) to add to input
				'field_attributes'	=> array(), // attr => value array for field container
				'field_class'		=> 'ctc-widget-no-bottom-margin', // class(es) to add to field container
				'custom_sanitize'	=> '', // function to do additional sanitization (or array( &$this, 'method' ))
				'custom_field'		=> '', // function for custom display of field input (or array( &$this, 'method' ))
				'taxonomies'		=> array(), // hide field if taxonomies are not supported
			),
			
			// Hierarchy
			'show_hierarchy' => array(
				'name'				=> '',
				'after_name'		=> '', // (Optional), (Required), etc.
				'desc'				=> '',
				'type'				=> 'checkbox', // text, textarea, checkbox, radio, select, number, url, image
				'radio_inline'		=> false, // show radio inputs inline or on top of each other
				'number_min'		=> '', // lowest possible value for number type
				'number_max'		=> '', // highest possible value for number type
				'checkbox_label'	=> _x( 'Show hierarchy', 'categories widget', 'church-theme' ), //show text after checkbox
				'options'			=> array(), // array of keys/values for radio or select
				'default'			=> true, // value to pre-populate option with (before first save or on reset)
				'no_empty'			=> false, // if user empties value, force default to be saved instead
				'allow_html'		=> false, // allow HTML to be used in the value (text, textarea)
				'attributes'		=> array(), // attributes to add to input element
				'class'				=> '', // class(es) to add to input
				'field_attributes'	=> array(), // attr => value array for field container
				'field_class'		=> 'ctc-widget-no-bottom-margin', // class(es) to add to field container
				'custom_sanitize'	=> '', // function to do additional sanitization (or array( &$this, 'method' ))
				'custom_field'		=> '', // function for custom display of field input (or array( &$this, 'method' ))
				'taxonomies'		=> array(), // hide field if taxonomies are not supported
			),
			
			// Dropdown
			'show_dropdown' => array(
				'name'				=> '',
				'after_name'		=> '', // (Optional), (Required), etc.
				'desc'				=> '',
				'type'				=> 'checkbox', // text, textarea, checkbox, radio, select, number, url, image
				'checkbox_label'	=> _x( 'Show as dropdown', 'categories widget', 'church-theme' ), //show text after checkbox
				'radio_inline'		=> false, // show radio inputs inline or on top of each other
				'number_min'		=> '', // lowest possible value for number type
				'number_max'		=> '', // highest possible value for number type
				'options'			=> array(), // array of keys/values for radio or select
				'default'			=> '', // value to pre-populate option with (before first save or on reset)
				'no_empty'			=> false, // if user empties value, force default to be saved instead
				'allow_html'		=> false, // allow HTML to be used in the value (text, textarea)
				'attributes'		=> array(), // attributes to add to input element
				'class'				=> '', // class(es) to add to input
				'field_attributes'	=> array(), // attr => value array for field container
				'field_class'		=> '', // class(es) to add to field container
				'custom_sanitize'	=> '', // function to do additional sanitization (or array( &$this, 'method' ))
				'custom_field'		=> '', // function for custom display of field input (or array( &$this, 'method' ))
				'taxonomies'		=> array(), // hide field if taxonomies are not supported
			),

		);
		
		return $fields;
	
	}
	
	/**
	 * Taxonomy Options
	 */

	function ctc_taxonomy_options() {
	
		$options = array();
	
		// Get exposed taxonomies
		$taxonomies = get_taxonomies( array(
			'public'	=> true,
			'show_ui'	=> true // weed out post_format
		), 'objects' );
		
		// Loop taxonomies
		foreach ( $taxonomies as $taxonomy_slug => $taxonomy_object ) {
		
			$taxonomy_name = $taxonomy_object->labels->name;
			
			// Set custom names for blog taxonomies
			if ( 'category' == $taxonomy_slug ) {
				$taxonomy_name = _x( 'Blog Categories', 'categories widget', 'church-theme' );
			} elseif ( 'post_tag' == $taxonomy_slug ) {
				$taxonomy_name = _x( 'Blog Tags', 'categories widget', 'church-theme' );
			}
		
			// Add to array
			$options[$taxonomy_slug] = $taxonomy_name;
		
		}
		
		// Return filtered
		return apply_filters( 'ctc_categories_widget_taxonomy_options', $options );
		
	}
	
	/**
	 * Redirect Dropdown URL
	 *
	 * Dropdown selection results in URL like http://yourname.com/?redirect_taxonomy=ccm_sermon_category&id=14
	 * This uses that query string to get permalink for that taxonomy and term
	 */

	function ctc_redirect_taxonomy() {

		// Redirect is being attempted on front page with valid taxonomy
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : '';
		if ( is_front_page() && ! empty( $_GET['redirect_taxonomy'] ) && taxonomy_exists( $_GET['redirect_taxonomy'] ) && ! empty( $id ) ) {

			// Get pretty URL
			$taxonomy = $_GET['redirect_taxonomy'];
			$term_url = get_term_link( $id, $taxonomy );		
			
			// Send to URL
			wp_redirect( $term_url, 301 );
			
		}

	}
		
}