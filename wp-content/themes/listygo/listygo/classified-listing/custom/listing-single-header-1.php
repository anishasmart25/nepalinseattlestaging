<?php 
use Rtcl\Helpers\Link;
use Rtrs\Models\Review; 
use RtclPro\Helpers\Fns;
use Rtcl\Helpers\Functions;
use radiustheme\listygo\Helper;
use radiustheme\listygo\Listing_Functions;
use Rtcl\Controllers\BusinessHoursController;
use Rtcl\Controllers\BusinessHoursController as BHS;
use RtclClaimListing\Helpers\Functions as ClaimFunctions;
global $listing;
global $wp_locale;
$images = $listing->get_images();

$business_hours = BHS::get_business_hours($listing->get_id());
if (BHS::openStatus($business_hours)) {
   $onoff = '<span class="onoff-status open"><i class="fas fa-check-circle"></i>'. esc_html__( 'Open Now', 'listygo' ).'</span>';
} else {
   $onoff = '<span class="onoff-status close"><i class="fas fa-times-circle"></i>'. esc_html__( 'Closed Now', 'listygo' ).'</span>';
}

$generalSettings = Functions::get_option( 'rtcl_general_settings' );
$appointment_label = !empty( $generalSettings['listygo_doctor_appointment_label'] ) ? $generalSettings['listygo_doctor_appointment_label'] : '';

$detailOption = Functions::get_option_item( 'rtcl_moderation_settings', 'display_options_detail', [] );
$show_phone = ! empty( $detailOption) && in_array( 'phone', $detailOption );
$show_address = ! empty( $detailOption) && in_array( 'address', $detailOption);

$location_type = Functions::location_type();
$address = get_post_meta( $listing->get_id(), 'address', true );
$geo_address = get_post_meta( $listing->get_id(), '_rtcl_geo_address', true );
$phone = get_post_meta( $listing->get_id(), 'phone', true );
$phone_url = str_replace( ' ', '', $phone );

$tags = Functions::get_listing_tag( $listing->get_id());

// Rating
if( class_exists( Review::class ) ){
    $average_rating = Review::getAvgRatings( get_the_ID() );
    $rating_count   = Review::getTotalRatings(  get_the_ID() );
} else {
    $average_rating   = $listing->get_average_rating();
    $rating_count     = $listing->get_rating_count();
}

$logo_id = get_post_meta( get_the_id(), 'listing_logo_img', true );

$abuse = Functions::get_option_item( 'rtcl_moderation_settings', 'has_report_abuse', '', 'checkbox' ) ? true : false;

?>
<div class="listingDetails-top listingDetails-top--bg white-bg header-banner-1">
    <div class="container">
        <div class="listing-header--top">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <div class="listingDetails-header">
                        <?php if ( !empty( $logo_id ) || $images ){ ?>
                        <figure class="listingDetails-header__thumb">
                            <?php 
                                if (!empty($logo_id)) {
                                    echo wp_get_attachment_image( $logo_id, 'full' );
                                } else {
                                    echo wp_kses_post( $listing->get_the_thumbnail( 'rtcl-gallery-thumbnail' ) );
                                } 
                            ?>
                        </figure>
                        <?php } ?>
                        <div class="listingDetails-header__content">
                            <div class="listingDetails-header__fetures">
                                <ul>
                                    <?php if ( $listing->has_category() && $listing->can_show_category() ){
                                        $category = $listing->get_categories();
                                        $category = end( $category ); 
                                        $term_id = $category->term_id;
                                    ?>
                                    <li>
                                        <a href="<?php echo esc_url( Link::get_category_page_link( $category ) ); ?>" class="listingDetails-header__tag">
                                            <?php echo wp_kses_post( Listing_Functions::listygo_cat_icon( $term_id, 'icon' ) ); ?>
                                            <?php echo esc_html( $category->name ); ?>
                                        </a>
                                    </li> 
                                        <?php }
                                        if ( $listing && Fns::is_enable_mark_as_sold() && Fns::is_mark_as_sold( $listing->get_id() ) ) {
                                            echo '<li class="rtcl-sold-out">' . apply_filters( 'rtcl_sold_out_header_text', esc_html__( "Sold Out", 'listygo' ) ) . '</li>';
                                        }
                                    ?>
                                    <li><?php $listing->the_badges(); ?></li>
                                </ul>
                            </div>
                           
                            <h1 class="listingDetails-header__heading">
                                <?php the_title(); ?>
                                 <?php if ( Functions::is_enable_business_hours() && ! empty( BusinessHoursController::get_business_hours( $listing->get_id() ) ) ){ ?>
                                    <?php echo wp_kses_post( $onoff ); ?>
                                <?php } ?>
                            </h1>
                            
                            <div class="listingDetails-header__fetures">
                              <ul class="info-list">
                              <?php 
                                $cats_ids = $listing->get_category_ids();
                                foreach ($cats_ids as $key => $value) {
                                    $category_id = $value;
                                }
                                $parent_cat = Listing_Functions::listygo_selected_category( $category_id );
                                $parent_cat = ( $parent_cat == 'doctor' || $parent_cat == 'doctors' ) ? true : false;

                                if ( $parent_cat == true && Listing_Functions::is_enable_doctor_listing() ){
                                    Helper::get_custom_listing_template( 'cfg-doctor' );
                                    if ( !empty($address || $geo_address) && $show_address == 1 ){ ?>
                                       <li class="meta-address">
                                          <span class="listingDetails-header__info">
                                             <?php echo Helper::map_icon(); ?>
                                             <?php 
                                                if ($location_type == 'geo') {
                                                    echo esc_html( $geo_address );
                                                } else {
                                                    echo esc_html( $address );
                                                }
                                             ?>
                                          </span>
                                       </li>
                                    <?php } else { if ( $listing->has_location() && $listing->can_show_location() ): ?>
                                        <li class="meta-address">
                                          <span class="listingDetails-header__info">
                                             <?php echo Helper::map_icon(); ?>
                                             <?php $listing->the_locations( true, false, false ); ?>
                                          </span>
                                       </li>
                                    <?php endif; }
                                } else {
                                   if ( $listing->has_location() && $listing->can_show_location() ): ?>
                                        <li class="meta-address">
                                            <span class="listingDetails-header__info">
                                                <?php echo Helper::map_icon(); ?>
                                                <?php $listing->the_locations( true, true ); ?>
                                            </span>
                                        </li>
                                    <?php endif; 
                                    
                                    if ( $listing->can_show_date() ) { ?>
                                        <li class="meta-date"><i class="fa-solid fa-calendar-days"></i> <?php $listing->the_time(); ?></li>
                                    <?php } if ( $listing->can_show_views() ){ ?>
                                        <li class="meta-view">
                                            <span>
                                            <?php echo Helper::view_icon(); ?>  
                                            <?php echo sprintf( _n( "View: %s", "Views: %s", $listing->get_view_counts(), 'listygo' ), 
                                                number_format_i18n( $listing->get_view_counts() ) ); ?>
                                            </span>
                                        </li>
                                    <?php }
                                    }
                                ?>
                                <?php if ( ! empty( $tags ) ) { ?>
                                    <li class="meta-tags">
                                        <i class="fa-solid fa-tag"></i>
                                        <?php echo Functions::get_listing_tag( $listing->get_id() ); ?>
                                    </li>
                                <?php } ?>
                              </ul>
                           </div>
                           <?php if ( $listing->can_show_price() ): ?>
                                <div class="product-price">
                                    <?php printf( "%s %s", esc_html_e( 'Price: ', 'listygo' ), $listing->get_price_html() ); ?>
                                </div>
                           <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-xl-end text-md-end text-md-center text-center">
                    <div class="listing-actions">
                        <ul>
                            <?php if ( Fns::is_enable_compare() ) { ?>
                                
                                <li class="meta-compare">
                                <?php
                                    $compare_ids = ! empty( $_SESSION['rtcl_compare_ids'] ) ? $_SESSION['rtcl_compare_ids'] : [];
                                    $selected_class = '';
                                    if ( is_array( $compare_ids ) && in_array( $listing->get_id(), $compare_ids ) ) {
                                        $selected_class = ' selected';
                                    }
                                ?>
                                <a class="rtcl-compare <?php echo esc_attr( $selected_class ); ?>" href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="<?php esc_attr_e( "Compare", "listygo" ) ?>" data-listing_id="<?php echo absint( $listing->get_id() ) ?>">
                                    <?php echo Helper::compare_icon(); ?>
                                </a>
                                </li>

                            <?php } if (Functions::is_enable_favourite()){ ?>

                            <li data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="<?php esc_attr_e( "Favourite", "listygo" ) ?>">
                                <?php echo Listing_Functions::get_favourites_link( $listing->get_id() ); ?>
                            </li>
                            <?php } if ( in_array('social_share', $detailOption)){ ?>
                            <li class="social-share-li" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="<?php esc_attr_e( "Share", "listygo" ) ?>"> 
                                <button class="listing-social-action" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">
                                    <?php echo Helper::share_icon2(); ?>
                                </button>   
                            </li>

                            <?php } if ( $abuse ){ ?>
                                <li class="report-abuse-li" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="<?php esc_attr_e( "Report", "listygo" ) ?>">
                                    <?php if ( is_user_logged_in() ): ?>
                                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rtcl-report-abuse-modal">
                                        <?php echo Helper::warning_icon(); ?>
                                    </a>
                                    <?php else: ?>
                                    <a href="javascript:void(0)" class="rtcl-require-login">
                                        <?php echo Helper::warning_icon(); ?>
                                    </a>
                                    <?php endif; ?>
                                </li>

                            <?php } if ( function_exists( 'rtclClaimListing' ) && ClaimFunctions::claim_listing_enable() ){ ?>
                                <li class='report-abuse-li' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="<?php esc_attr_e( "Claim", "listygo" ) ?>">
                                    <?php if ( is_user_logged_in() ): ?>
                                        <span data-bs-toggle="tooltip" data-original-title="<?php echo esc_html( ClaimFunctions::get_claim_action_title() ); ?>">
                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rtcl-claim-listing-modal">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </a>
                                        </span>
                                    <?php else: ?>
                                        <a href="javascript:void(0)" data-bs-toggle="tooltip" class="rtcl-require-login" data-original-title="<?php echo esc_html( ClaimFunctions::get_claim_action_title() ); ?>">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php do_action( 'rtcl_single_listing_after_action', $listing->get_id() ); ?>

<?php 
    if ( in_array('social_share', $detailOption)){
        Listing_Functions::get_share_link(); 
    }
?>