<?php
/**
 *
 * @author        RadiusTheme
 * @package    classified-listing/templates
 * @version     3.0.1
 *
 * @var Form $form
 * @var array $fields
 * @var int $listing_id
 */


use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FBField;

if ( !is_a( $form, Form::class ) ) {
	return;
}

$fields = $form->getListableFields();

if ( count( $fields ) ) :
	ob_start();
	foreach ( $fields as $fieldName => $field ) {
		$field = new FBField( $field );
		$value = $field->getFormattedCustomFieldValue( $listing_id );
		if ( $value ) :
			?>
			<div class='rtcl-listable-item'>
				<span class='listable-label'><?php echo esc_html( $field->getLabel() ) ?></span>
				<span class='listable-value'>
					<?php
					if ( in_array( $field->getElement(), [ 'select', 'radio', 'checkbox' ] ) ) {
						if ( $field->getElement() === 'checkbox' ) {
							$_value = [];
							foreach ( $value as $item ) {
								$_value[] = !empty( $item['label'] ) ? $item['label'] : '';
							}
							$value = !empty( $_value ) ? implode( ', ', $_value ) : '';
						} else {
							$value = is_array( $value ) && !empty( $value['label'] ) ? $value['label'] : '';
						}
					}
					Functions::print_html( $value );
					?>
				</span>
			</div>
		<?php endif;
	}

	$fields_html = ob_get_clean();
	if ( $fields_html ) {
		printf( '<div class="rtcl-listable">%s</div>', $fields_html );
	}
endif;
