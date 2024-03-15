<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Listygo_Core;
use radiustheme\listygo\Helper;
use radiustheme\listygo\Listing_Functions;

$search_style = $data['listing_banner_search_style'];
$listingTypes = $data['getListingTypes'];
?>

<!-- Hero layout1 -->
<div class="listygo-search-form search-form-2 search-form-5 car-search-form">
	<?php Helper::get_custom_listing_template( 'listing-search-3', true, compact('search_style', 'listingTypes') ); ?>
</div>
<!-- Hero layout1 End -->