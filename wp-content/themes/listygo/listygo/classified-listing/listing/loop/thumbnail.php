<?php
use Rtcl\Helpers\Link;
use Rtrs\Models\Review;
use RtclPro\Helpers\Fns;
use Rtcl\Helpers\Functions;
use radiustheme\listygo\Helper;
use radiustheme\listygo\Listing_Functions;
use Rtcl\Controllers\Hooks\TemplateHooks;
use Rtcl\Controllers\BusinessHoursController;
use Rtcl\Controllers\BusinessHoursController as BHS;

global $listing;

if (!class_exists('RtclPro')) return;

// Rating
if( class_exists( Review::class ) ){
    $average_rating = Review::getAvgRatings( get_the_ID() );
    $rating_count   = Review::getTotalRatings(  get_the_ID() );
} else {
    $average_rating = $listing->get_average_rating();
    $rating_count   = $listing->get_rating_count();
}

$business_hours = BHS::get_business_hours($listing->get_id());
if (BHS::openStatus($business_hours)) {
    $onoff = '<div class="item-status status-open active">'. esc_html__( 'Open Now', 'listygo' ).'</div>';
} else {
    $onoff = '<div class="item-status status-close">'. esc_html__( 'Closed Now', 'listygo' ).'</div>';
}

?>
<div class="item-img">
    <div class="rt-categories">
        <?php if ( $listing->has_category() && $listing->can_show_category() ):
            $category = $listing->get_categories();
            $category = end( $category );
            $term_id = $category->term_id;
        ?>
        <a href="<?php echo esc_url( Link::get_category_page_link( $category ) ); ?>" class="category-list">
            <?php echo wp_kses_post( Listing_Functions::listygo_cat_icon( $term_id, 'icon' ) ); ?>
            &nbsp;<?php echo esc_html( $category->name ); ?>
        </a>
        <?php endif; ?>
        <?php TemplateHooks::loop_item_badges(); ?>
    </div>
    
    <div class="open-close-location-status">
        <?php if ( Functions::is_enable_business_hours() && ! empty( BusinessHoursController::get_business_hours( $listing->get_id() ) ) ){
            echo wp_kses_post( $onoff ); 
        }
        ?>
    </div>

    <div class="listing-thumb">
        <a href="<?php the_permalink(); ?>" class="rtcl-media grid-view-img bg--gradient-50"><?php echo wp_kses_post( $listing->get_the_thumbnail( 'rtcl-thumbnail' ) ); ?></a>
        <a href="<?php the_permalink(); ?>" class="rtcl-media list-view-img bg--gradient-50"><?php echo wp_kses_post( $listing->get_the_thumbnail( 'listygo-size-1' ) ); ?></a>
	</div>
    <?php 
        Helper::get_listing_author_info( $listing );
    ?>
</div>