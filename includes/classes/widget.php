<?php
/**
 * churchthemes.com Widget Layer
 *
 * The framework widgets extend this class which extends WP_Widget.
 * This extra layer adds methods for automatic field output, field filtering, sanitization, updating and front-end display via template.
 */
 
class CTC_Widget extends WP_Widget {

	/**
	 * Constructor
	 */

	function __construct( $id_base = false, $name, $widget_options = array(), $control_options = array() ) {
	
		parent::__construct( $id_base, $name, $widget_options, $control_options );

		// Prepare fields
		add_action( 'init', array( &$this, 'ctc_prepare_fields' ) ); // earlier than init_widgets

	}
	
	/**
	 * Prepare fields
	 *
	 * Filter fields and make them available.
	 */

	function ctc_prepare_fields() {
	
		// Get fields from extending class
		$fields = array();
		if ( method_exists( get_called_class(), 'ctc_fields' ) ) {
			$fields = $this->ctc_fields();
		}
	
		// Fill array of visible fields with all by default
		$visible_fields = array();
		foreach ( $fields as $id => $field ) {
			$visible_fields[] = $id;
		}
		
		// Let themes/plugins set explicit visibility for fields for specific widget
		$visible_fields = apply_filters( 'ctc_widget_visible_fields-' . $this->id_base, $visible_fields, $this->id_base );

		// Let themes/plugins override specific data for field of specific post type
		$field_overrides = apply_filters( 'ctc_widget_field_overrides-' . $this->id_base, array(), $this->id_base ); // by default no overrides

		// Loop fields to modify them with filtered data
		foreach ( $fields as $id => $field ) {

			// Selectively override field data based on filtered array
			if ( ! empty( $field_overrides[$id] ) && is_array( $field_overrides[$id] ) ) {
				$fields[$id] = array_merge( $field, $field_overrides[$id] ); // merge filtered in data over top existing data
			}
			
			// Set visibility of field based on filtered or unfiltered array
			$fields[$id]['hidden'] = ! in_array( $id, (array) $visible_fields ) ? true : false; // set hidden true if not in array
			
			// Set visibility of field based on required taxonomy support (in case unsupported by theme or via plugin settings, etc.)
			if ( ! empty( $fields[$id]['taxonomies'] ) ) {

				// Loop taxonomies
				foreach ( (array) $fields[$id]['taxonomies'] as $taxonomy_name ) {

					// Taxonomy not supported by theme (or possibly disabled via Church Content Manager)
					if ( ! ctc_taxonomy_supported( $taxonomy_name ) ) { // check show_ui
						$fields[$id]['hidden'] = true;
						break; // one strike and you're out
					}
				
				}
				
			}
			
		}
	
		// Make fields array accessible
		$this->ctc_fields = $fields;
	
	}

	/**
	 * Back-end widget form
	 */

	function form( $instance ) {
		
		// Loop fields
		$fields = $this->ctc_fields;
		foreach( $fields as $id => $field ) {
			
			/**
			 * Field Data
			 */
		
			// Store data in array so custom output callback can use it
			$data = array();

			// Get field config
			$data['id'] = $id;
			$data['field'] = $field;

			// Prepare strings
			$data['default'] = isset( $data['field']['default'] ) ? $data['field']['default'] : '';
			$data['value'] = isset( $instance[$id] ) ? $instance[$id] : $data['default']; // get saved value or use default if first save
			$data['esc_value'] = esc_attr( $data['value'] );
			$data['esc_element_id'] = $this->get_field_id( $data['id'] );
			
			// Prepare styles for elements (core WP styling)
			$default_classes = array(
				'text'			=> 'regular-text',
				'url'			=> 'regular-text',
				'textarea'		=> '',
				'checkbox'		=> '',
				'radio'			=> '',
				'radio_inline'	=> '',
				'select'		=> '',
				'number'		=> 'small-text',
				'image'			=> '',
				
			);
			$classes = array();
			$classes[] = 'ctc-widget-' . $data['field']['type'];
			if ( ! empty( $default_classes[$data['field']['type']] ) ) {
				$classes[] = $default_classes[$data['field']['type']];
			}
			if ( ! empty( $data['field']['class'] ) ) {
				$classes[] = $data['field']['class'];
			}
			$data['classes'] = implode( ' ', $classes );
			
			// Common attributes
			$data['common_atts'] = 'name="' . $this->get_field_name( $data['id'] ) . '" class="' . esc_attr( $data['classes'] ) . '"';
			if ( ! empty( $data['field']['attributes'] ) ) { // add custom attributes
				foreach( $data['field']['attributes'] as $attr_name => $attr_value ) {
					$data['common_atts'] .= ' ' . $attr_name . '="' . esc_attr( $attr_value ) . '"';
				}		
			}			

			// Field container classes
			$data['field_class'] = array();
			$data['field_class'][] = 'ctc-widget-field';
			$data['field_class'][] = 'ctc-widget-field-' . $data['id'];
			if ( ! empty( $data['field']['hidden'] ) ) { // Hidden (for internal use only, via prepare() filter)
				$data['field_class'][] = 'ctc-widget-hidden';				
			}
			if ( ! empty( $data['field']['field_class'] ) ) {
				$data['field_class'][] = $data['field']['field_class']; // append custom classes
			}
			$data['field_class'] = implode( ' ', $data['field_class'] );
			
			// Field container styles
			$data['field_attributes'] = '';
			if ( ! empty( $data['field']['field_attributes'] ) ) { // add custom attributes
				foreach( $data['field']['field_attributes'] as $attr_name => $attr_value ) {
					$data['field_attributes'] .= ' ' . $attr_name . '="' . esc_attr( $attr_value ) . '"';
				}		
			}
		
			/**
			 * Form Input
			 */
			 
			// Use custom function to render custom field content
			if ( ! empty( $data['field']['custom_field'] ) ) {
				$input = call_user_func( $data['field']['custom_field'], $data );
			}
		
			// Standard output based on type
			else {
			
				// Switch thru types to render differently
				$input = '';
				switch ( $data['field']['type'] ) {
				
					// Text
					// URL
					case 'text':
					case 'url': // same as text
					
						$input = '<input type="text" ' . $data['common_atts'] . ' id="' . $data['esc_element_id'] . '" value="' . $data['esc_value'] . '" />';
					
						break;

					// Textarea
					case 'textarea':
					
						$input = '<textarea ' . $data['common_atts'] . ' id="' . $data['esc_element_id'] . '">' . esc_textarea( $data['value'] ) . '</textarea>';
						
						// special esc func for textarea
					
						break;

					// Checkbox
					case 'checkbox':
					
						$input  = '<input type="hidden" ' . $data['common_atts'] . ' value="" />'; // causes unchecked box to post empty value (helps with default handling)
						$input .= '<label for="' . $data['esc_element_id'] . '">';
						$input .= '	<input type="checkbox" ' . $data['common_atts'] . ' id="' . $data['esc_element_id'] . '" value="1"' . checked( '1', $data['value'], false ) . '/>';
						if ( ! empty( $data['field']['checkbox_label'] ) ) {
							$input .= ' ' . $data['field']['checkbox_label'];
						}
						$input .= '</label>';
						
						break;

					// Radio
					case 'radio':
					
						if ( ! empty( $data['field']['options'] ) ) {
						
							foreach( $data['field']['options'] as $option_value => $option_text ) {
							
								$esc_radio_id = $data['esc_element_id'] . '-' . $option_value;
							
								$input .= '<div' . ( ! empty( $data['field']['radio_inline'] ) ? ' class="ctc-widget-radio-inline"' : '' ) . '>';				
								$input .= '	<label for="' . $esc_radio_id . '">';
								$input .= '		<input type="radio" ' . $data['common_atts'] . ' id="' . $esc_radio_id . '" value="' . esc_attr( $option_value ) . '"' . checked( $option_value, $data['value'], false ) . '/> ' . esc_html( $option_text );
								$input .= '	</label>';
								$input .= '</div>';
								
							}
							
						}
					
						break;

					// Select
					case 'select':
					
						if ( ! empty( $data['field']['options'] ) ) {
						
							$input .= '<select ' . $data['common_atts'] . ' id="' . $data['esc_element_id'] . '">';
							foreach( $data['field']['options'] as $option_value => $option_text ) {
								$input .= '<option value="' . esc_attr( $option_value ) . '" ' . selected( $option_value, $data['value'], false ) . '> ' . esc_html( $option_text ) . '</option>';
							}
							$input .= '</select>';
							
						}
					
						break;
				
					// Number
					case 'number':

						// Min and max attributes
						$min = isset( $field['number_min'] ) && '' !== $field['number_min'] ? (int) $field['number_min'] : ''; // force number if set
						$max = isset( $field['number_max'] ) && '' !== $field['number_max'] ? (int) $field['number_max'] : ''; // force number if set
										
						$input = '<input type="number" ' . $data['common_atts'] . ' id="' . $data['esc_element_id'] . '" value="' . $data['esc_value'] . '" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" />';
						
						break;

					// Image
					case 'image':
					
						// Is image set and still exists?
						$value_container_classes = 'ctc-widget-image-unset';
						if ( ! empty( $data['value'] ) && wp_get_attachment_image_src( $data['value'] ) ) {
							$value_container_classes = 'ctc-widget-image-set';
						}

						// Hidden input for image ID
						$input .= '<input type="hidden" ' . $data['common_atts'] . ' id="' . $data['esc_element_id'] . '" value="' . $data['esc_value'] . '" />';

						// Show image
						$input .= '<div class="ctc-widget-image-preview">' . wp_get_attachment_image( $data['value'], 'medium' ) . '</div>';

						// Button to open media library
						$input .= '<a href="#" class="button ctc-widget-image-choose" data-ctc-field-id="' . $data['esc_element_id'] . '">' . _x( 'Choose Image', 'widget image field', 'church-theme' ) . '</a>';		

						// Button to remove image
						$input .= '<a href="#" class="button ctc-widget-image-remove">' . _x( 'Remove Image', 'widget image field', 'church-theme' ) . '</a>';		

						break;

				}
			
			}

			/**
			 * Field Container
			 */

			// Output field
			if ( ! empty( $input ) ) { // don't render if type invalid
				
				?>
				<div class="<?php echo esc_attr( $data['field_class'] ); ?>"<?php echo $data['field_attributes']; ?>>
				
					<div class="ctc-widget-name">
					
						<?php if ( ! empty( $data['field']['name'] ) ) : ?>
						
							<?php echo esc_html( $data['field']['name'] ); ?>
							
							<?php if ( ! empty( $data['field']['after_name'] ) ) : ?>
								<span><?php echo esc_html( $data['field']['after_name'] ); ?></span>
							<?php endif; ?>
							
						<?php endif; ?>
						
					</div>
					
					<div class="ctc-widget-value<?php echo ! empty( $value_container_classes ) ? ' ' . $value_container_classes : ''; ?>">
					
						<?php echo $input; ?>
						
						<?php if ( ! empty( $data['field']['desc'] ) ) : ?>
						<p class="description">
							<?php echo $data['field']['desc']; ?>
						</p>
						<?php endif; ?>
						
					</div>
					
				</div>
				<?php
				
			}
			
		}		
		
	}

	/**
	 * Sanitize field values
	 *
	 * Used before saving and before providing instance to widget template.
	 */
	
	function ctc_sanitize( $instance ) {

		global $allowedposttags;
		
		// Array to add sanitized values to
		$sanitized_instance = array();
		
		// Loop valid fields to sanitize
		$fields = $this->ctc_fields;
		foreach( $fields as $id => $field ) {

			// Get posted value
			$input = isset( $instance[$id] ) ? $instance[$id] : '';
			
			// General sanitization
			$output = trim( stripslashes( $input ) );

			// Sanitize based on type
			switch ( $field['type'] ) {
			
				// Text
				// Textarea
				case 'text':
				case 'textarea':

					// Strip tags if config does not allow HTML
					if ( empty( $field['allow_html'] ) ) {
						$output = trim( strip_tags( $output ) );
					}
			
					// Sanitize HTML in case used (remove evil tags like script, iframe) - same as post content
					$output = stripslashes( wp_filter_post_kses( addslashes( $output ), $allowedposttags ) );
					
					break;

				// Checkbox
				case 'checkbox':

					$output = ! empty( $output ) ? '1' : '';
				
					break;

				// Radio
				// Select
				case 'radio':
				case 'select':
				
					// If option invalid, blank it so default will be used
					if ( ! isset( $field['options'][$output] ) ) {
						$output = '';
					}
				
					break;
			
				// Number
				case 'number':
				
					// Force number
					$output = (int) $output;
					
					// Enforce minimum value
					$min = isset( $field['number_min'] ) && '' !== $field['number_min'] ? (int) $field['number_min'] : ''; // force number if set
					if ( '' !== $min && $output < $min ) { // allow 0, don't process if no value given ('')
						$output = $min;					
					}
					
					// Enforce maximum value
					$max = isset( $field['number_max'] ) && '' !== $field['number_max'] ? (int) $field['number_max'] : ''; // force number if set
					if ( '' !== $max && $output > $max ) { // allow 0, don't process if no value given ('')
						$output = $max;
					}
				
					break;
					
				// URL
				case 'url':
				
					$output = esc_url_raw( $output ); // force valid URL or use nothing
					
					break;

				// Image
				case 'image':
				
					// Sanitize attachment ID
					$output = absint( $output );

					// Set empty if value is 0, attachment does not exist, or is not an image
					if ( empty( $output ) || ! wp_get_attachment_image_src( $output ) ) {
						$output = '';
					}
					
					break;

			}

			// Run additional custom sanitization function if field config requires it
			if ( ! empty( $field['custom_sanitize'] ) ) {
				$output = call_user_func( $field['custom_sanitize'], $output );
			}

			// Sanitization left value empty, use default if empty not allowed
			$output = trim( $output );
			if ( empty( $output ) && ! empty( $field['default'] ) && ! empty( $field['no_empty'] ) ) {
				$output = $field['default'];
			}
			
			// Add output to instance array
			$sanitized_instance[$id] = $output;
		 
		}
		
		// Return for saving
		return $sanitized_instance;

	}
	
	/**
	 * Save sanitized form values
	 */
	
	function update( $new_instance, $old_instance ) {

		// Sanitize values
		$instance = $this->ctc_sanitize( $new_instance );
		
		// Return for saving
		return $instance;
	
	}
	
	/**
	 * Front-end display of widget
	 *
	 * Load template from parent or child theme if exists.
	 */

	function widget( $args, $instance ) {

		global $post; // setup_postdata() needs this

		// Available widgets
		$widgets = ctc_fw_widgets();

		// Get template filename
		$template_file = $widgets[$this->id_base]['template_file'];

		// Check if template exists
		if ( $template_path = locate_template( CTC_WIDGET_DIR . '/' . $template_file ) ) { // false if does not exist
		
			// Sanitize widget instance (field values) before loading template
			$instance = $this->ctc_sanitize( $instance );
		
			// Make instance available to other methods used by template (e.g. get_posts())
			$this->ctc_instance = $instance;

			// Load template with globals available (inlike locate_template())
			include $template_path;

		}
		
	}	
		
}