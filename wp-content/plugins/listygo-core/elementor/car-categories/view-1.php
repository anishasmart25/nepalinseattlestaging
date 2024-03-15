<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */
use radiustheme\listygo\Helper;
use radiustheme\listygo\Listing_Functions;

$car_cats = $data['car_categories'];
?>

<!-- Categories -->
<div class="hero-categories-wrap">
	<span class="hero-categories--title"><?php echo esc_html( $data['title'] ); ?></span>
	<?php
		$terms = $car_cats;
		if(!empty(Helper::car_categories_slug())){ ?>
			<div class="hero-categories hero-categories--style2">
				<?php   	
				foreach ($terms as $key => $value) {
					if (!empty($value)) {
					$term_id = get_term( $value )->term_id;
					$term_icon = get_term_meta( $term_id, '_rtcl_icon', true );
				?>
				<a href="<?php echo esc_url( get_term_link( get_term( $value ), get_term( $value )->name ) ); ?>" class="hero-categoriesBlock hero-categoriesBlock--style2">
					<?php echo wp_kses_post( Listing_Functions::listygo_cat_icon( $term_id, '' ) ); ?>
					<?php echo esc_html( get_term( $value )->name ); ?>
				</a>
				<?php } } ?>
			</div>
	<?php } ?>
</div>
<!-- Categories End -->