<?php
/**
 * WordPress Fields API Meta Box Section class
 *
 * @package    WordPress
 * @subpackage Fields API
 */

/**
 * Fields API Meta Box Section class.
 */
class WP_Fields_API_Meta_Box_Section extends WP_Fields_API_Section {

	/**
	 * Meta box context
	 *
	 * @var string
	 */
	public $mb_context = 'advanced';

	/**
	 * Meta box priority
	 *
	 * @var string
	 */
	public $mb_priority = 'default';

	/**
	 * Meta box callback arguments
	 *
	 * @var array
	 */
	public $mb_callback_args = array();

	/**
	 * Add meta boxes for sections
	 *
	 * @param string $object_name Object name, if 'comment' then it's the comment object type
	 */
	public static function add_meta_boxes( $object_name ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$object_type = 'post';

		if ( 'comment' == $object_name ) {
			$object_type = 'comment';
		}

		$form_id = $object_type . '-edit';

		// Get registered sections
		$sections = $wp_fields->get_sections( $object_type, $object_name, $form_id );

		foreach ( $sections as $section ) {
			// Skip non meta boxes
			if ( ! is_a( $section, 'WP_Fields_API_Meta_Box_Section' ) ) {
				continue;
			}

			/**
			 * @var $section WP_Fields_API_Meta_Box_Section
			 */

			// Add primary callback arguments
			$section->mb_callback_args['fields_api']  = true;
			$section->mb_callback_args['object_type'] = $object_type;
			$section->mb_callback_args['section']     = $section;

			// Add meta box
			add_meta_box(
				$section->id,
				$section->title,
				array( 'WP_Fields_API_Meta_Box_Section', 'render_meta_box' ),
				null,
				$section->mb_context,
				$section->mb_priority,
				$section->mb_callback_args
			);
		}

	}

	/**
	 * Render meta box output for section
	 *
	 * @param WP_Post|WP_Comment $object Current Object
	 * @param array              $box    Meta box options
	 */
	public function render_meta_box( $object, $box ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		if ( empty( $box['args'] ) || empty( $box['args']['fields_api'] ) || empty( $box['args']['section'] ) ) {
			return;
		}

		/**
		 * @var $section WP_Fields_API_Meta_Box_Section
		 */

		$section = $box['args']['section'];

		$item_id     = 0;
		$object_name = null;

		if ( ! empty( $object->ID ) ) {
			// Get Post ID and type
			$item_id     = $object->ID;
			$object_name = $object->post_type;
		} elseif ( ! empty( $object->comment_ID ) ) {
			// Get Comment ID and type
			$item_id     = $object->comment_ID;
			$object_name = $object->comment_type;
		}

		$form = $section->form;

		if ( ! is_object( $form ) && ! empty( $box['args']['object_type'] ) ) {
			$form = $wp_fields->get_form( $box['args']['object_type'], $form, $object_name );
		}

		if ( is_a( $form, 'WP_Fields_API_Form' ) ) {
			$form->render_section( $section, $item_id, $object_name );
		}

	}

}