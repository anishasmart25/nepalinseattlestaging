<?php
/**
 * Map content
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 *
 * @var $listing Listing
 */

if (!isset($listing)) {
    global $listing;
}

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<div class="rtcl-map-popup">
    <a href="<?php $listing->the_permalink() ?>" class="rtcl-map-popup-media text-overflow" target="_blank"><?php $listing->the_thumbnail() ?></a>
    <div class="rtcl-map-popup-content">
        <h5 class="rtcl-map-item-title">
            <a class="text-overflow" href="<?php $listing->the_permalink() ?>" target="_blank"><?php $listing->the_title() ?>
            </a></h5>
        <div class="bottom-rtcl-meta flex-wrap">
            <?php if ( $listing->can_show_price() ): ?>
                <div class="product-price"><?php printf( "%s", $listing->get_price_html() ); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
