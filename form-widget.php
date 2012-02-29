<?php

class IWAJAX_Contact_Widget extends WP_Widget {
	
	/** constructor */
	function IWAJAX_Contact_Widget() {
		
		parent::WP_Widget( false, $name = 'Contact Form', array( 'description' => 'Use this widget to display a custom AJAX Contact Form.' ) );
		
	}
	
	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		
		extract( $args );
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;
		
		$form_id = $instance['form_id'];
		
		echo iwacontact_get_contact_form( $form_id );
		
		echo $after_widget;
		
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['form_id'] = strip_tags( $new_instance['form_id'] );
		return $instance;
		
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		
		$title = ( key_exists( 'title', $instance ) ) ? esc_attr( $instance['title'] ) : 'Contact Form';
		$form_id = ( key_exists( 'form_id', $instance ) ) ? esc_attr( $instance['form_id'] ) : '';
		
		$contact_forms = new WP_Query();
		$contact_forms->query( array( 'post_type' => 'iwacontactform' ) );
		$form_array = array();
		while ( $contact_forms->have_posts() ) {
			$contact_forms->the_post();
			$form = array(
				'id' => get_the_id(),
				'title' => get_the_title()
			);
			array_push( $form_array, $form );
		}
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Widget Title: </label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'form_id' ); ?>">Contact Form: </label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>">
				<?php echo $this->getSelectOptions( $form_array, $form_id ); ?>
			</select>
		</p>
		<?php
		
	}
	
	/**
	 * Get select box options
	 *
	 * @param array $forms An array of associative form item arrays
	 * @param string $form_id The currently selected form id
	 **/
	function getSelectOptions( $forms, $form_id ) {
		
		$options = '';
		
		foreach ( $forms as $form )
			$options .= '<option value="' . $form['id'] . '" ' . ( ( $form['id'] == $form_id ) ? 'selected="selected"' : '' ) . '>' . $form['title'] . '</option>';
		
		return $options;
		
	}

}

add_action( 'widgets_init', create_function( '', 'return register_widget("IWAJAX_Contact_Widget");' ) );