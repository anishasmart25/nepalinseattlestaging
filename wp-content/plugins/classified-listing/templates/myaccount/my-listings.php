<?php
/**
 *Manage Listing by user
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var WP_Query $rtcl_query
 */


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Pagination;

global $post;
?>

<div class="rtcl-manage-my-listings">

	<?php do_action( 'rtcl_my_account_before_my_listing', $rtcl_query ); ?>

	<!-- header here -->
	<div class="rtcl-action-wrap">
		<div class="rtcl-my-listings-search-form">
			<form action="<?php echo esc_url( Link::get_account_endpoint_url( "listings" ) ); ?>" class="form-inline">
				<input type="text" id="search-ml" name="u" class="rtcl-form-control"
					   placeholder="<?php esc_attr_e( "Search by title", 'classified-listing' ); ?>"
					   value="<?php echo isset( $_GET['u'] ) ? esc_attr( wp_unslash( $_GET['u'] ) ) : ''; ?>">
				<button type="submit">
					<svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M8.65429 17.2954C3.88229 17.2954 0 13.4161 0 8.64769C0 3.87933 3.88229 0 8.65429 0C13.4263 0 17.3086 3.87933 17.3086 8.64769C17.3086 13.4161 13.4263 17.2954 8.65429 17.2954ZM8.65429 1.63937C4.78693 1.63937 1.64062 4.78328 1.64062 8.64769C1.64062 12.5121 4.78693 15.656 8.65429 15.656C12.5216 15.656 15.668 12.5121 15.668 8.64769C15.668 4.78328 12.5216 1.63937 8.65429 1.63937ZM20.7598 20.76C21.0801 20.4398 21.0801 19.9208 20.7598 19.6007L17.0889 15.9326C16.7685 15.6125 16.2491 15.6125 15.9287 15.9326C15.6084 16.2527 15.6084 16.7718 15.9287 17.0919L19.5996 20.76C19.7598 20.92 19.9697 21 20.1797 21C20.3897 21 20.5995 20.92 20.7598 20.76Z"
							fill="#646464"/>
					</svg>
				</button>
				<?php Functions::query_string_form_fields( null, [ 'submit', 'paged', 'u' ] ); ?>
			</form>
		</div>
		<?php if ( apply_filters( 'rtcl_add_new_listing_button', true ) ) { ?>
			<div class="rtcl-add-new-listing">
				<a href="<?php echo esc_url( Link::get_listing_form_page_link() ); ?>"
				   class="btn btn-success"><?php esc_html_e( 'Add Listing', 'classified-listing' ); ?></a>
			</div>
		<?php } ?>
	</div>

	<?php if ( $rtcl_query->have_posts() ): ?>
		<div class="rtcl-MyAccount-content-inner">
			<table class="rtcl-my-listing-table">
				<thead>
				<tr>
					<th><?php _e( 'Thumbnail', 'classified-listing' ); ?></th>
					<th class="title-cell"><?php _e( 'Title', 'classified-listing' ); ?></th>
					<th class="price-cell list-on-responsive"><?php _e( 'Price', 'classified-listing' ); ?></th>
					<th class="list-on-responsive"><?php _e( 'Expires On', 'classified-listing' ); ?></th>
					<th class="list-on-responsive"><?php _e( 'Status', 'classified-listing' ); ?></th>
					<th class="list-on-responsive"><?php _e( 'Action', 'classified-listing' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php while ( $rtcl_query->have_posts() ) : $rtcl_query->the_post();
					$post_meta  = get_post_meta( $post->ID );
					$listing    = rtcl()->factory->get_listing( $post->ID );
					$is_top     = (bool) get_post_meta( $listing->get_id(), '_top', true );
					$is_bump_up = (bool) get_post_meta( $listing->get_id(), '_bump_up', true );
					?>
					<tr>
						<td>
							<div class="listing-thumb">
								<a href="<?php the_permalink(); ?>"><?php $listing->the_thumbnail(); ?></a>
								<?php do_action( 'rtcl_my_listing_after_listing_thumb', $listing ); ?>
							</div>
						</td>
						<td class="title-cell">
							<div class="rtcl-ad-details">
								<a class="listing-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								<?php $listing->the_badges(); ?>
								<ul class="rtcl-meta">
									<li>
										<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd"
												  d="M7.99941 1.60002C4.46479 1.60002 1.59941 4.4654 1.59941 8.00002C1.59941 11.5346 4.46479 14.4 7.99941 14.4C11.534 14.4 14.3994 11.5346 14.3994 8.00002C14.3994 4.4654 11.534 1.60002 7.99941 1.60002ZM0.399414 8.00002C0.399414 3.80266 3.80205 0.400024 7.99941 0.400024C12.1968 0.400024 15.5994 3.80266 15.5994 8.00002C15.5994 12.1974 12.1968 15.6 7.99941 15.6C3.80205 15.6 0.399414 12.1974 0.399414 8.00002ZM7.99941 3.20002C8.33078 3.20002 8.59941 3.46865 8.59941 3.80002V7.6292L11.0677 8.86337C11.3641 9.01156 11.4843 9.37196 11.3361 9.66835C11.1879 9.96474 10.8275 10.0848 10.5311 9.93668L7.73108 8.53668C7.52781 8.43505 7.39941 8.22729 7.39941 8.00002V3.80002C7.39941 3.46865 7.66804 3.20002 7.99941 3.20002Z"
												  fill="currentColor"/>
										</svg>
										<?php $listing->the_time(); ?>
									</li>
									<?php if ( $listing->has_category() ): ?>
										<li>
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" clip-rule="evenodd"
													  d="M0.399414 1.00002C0.399414 0.668653 0.668044 0.400024 0.999414 0.400024H8.30189C8.46104 0.400024 8.61368 0.46326 8.72621 0.575815L15.0003 6.85154C15.384 7.23769 15.5994 7.76 15.5994 8.30441C15.5994 8.84881 15.384 9.37113 15.0003 9.75727L14.999 9.75853L9.76339 14.9955C9.57204 15.1871 9.34479 15.3392 9.09464 15.4429C8.84448 15.5466 8.57634 15.6 8.30554 15.6C8.03474 15.6 7.76659 15.5466 7.51644 15.4429C7.26649 15.3392 7.03941 15.1874 6.84815 14.996C6.8483 14.9961 6.84799 14.9958 6.84815 14.996L0.575342 8.72886C0.462703 8.61633 0.399414 8.46363 0.399414 8.30441V1.00002ZM1.59941 1.60002V8.05572L7.69631 14.1471C7.77623 14.2271 7.87162 14.2911 7.97607 14.3344C8.08052 14.3777 8.19247 14.4 8.30554 14.4C8.4186 14.4 8.53056 14.3777 8.635 14.3344C8.73945 14.2911 8.83436 14.2276 8.91428 14.1476L14.1491 8.91138C14.1493 8.91119 14.1489 8.91157 14.1491 8.91138C14.309 8.75015 14.3994 8.53162 14.3994 8.30441C14.3994 8.0772 14.3096 7.85924 14.1497 7.69801C14.1499 7.6982 14.1495 7.69782 14.1497 7.69801L8.05331 1.60002H1.59941ZM4.05065 4.65222C4.05065 4.32084 4.31928 4.05222 4.65065 4.05222H4.65795C4.98932 4.05222 5.25795 4.32084 5.25795 4.65222C5.25795 4.98359 4.98932 5.25222 4.65795 5.25222H4.65065C4.31928 5.25222 4.05065 4.98359 4.05065 4.65222Z"
													  fill="currentColor"/>
											</svg>
											<?php $listing->the_categories( true, true ); ?>
										</li>
									<?php endif; ?>
									<?php if ( $listing->has_location() && apply_filters( 'rtcl_my_listing_location_display', false ) ): ?>
										<li>
											<svg width="13" height="16" viewBox="0 0 13 16" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" clip-rule="evenodd"
													  d="M6.49941 1.60002C5.20787 1.60002 3.96406 2.13406 3.04309 3.09309C2.12132 4.05295 1.59941 5.3598 1.59941 6.7273C1.59941 8.7224 2.84381 10.6471 4.19323 12.1303C4.85707 12.86 5.52277 13.4571 6.02302 13.8719C6.20793 14.0253 6.3696 14.1532 6.49941 14.2531C6.62922 14.1532 6.7909 14.0253 6.9758 13.8719C7.47605 13.4571 8.14176 12.86 8.8056 12.1303C10.155 10.6471 11.3994 8.7224 11.3994 6.7273C11.3994 5.3598 10.8775 4.05295 9.95571 3.09309C9.03481 2.13406 7.79095 1.60002 6.49941 1.60002ZM6.49941 15C6.15725 15.4929 6.1571 15.4928 6.15692 15.4926L6.15519 15.4914L6.15114 15.4886L6.13717 15.4788C6.12529 15.4704 6.10835 15.4583 6.08667 15.4427C6.04332 15.4114 5.98101 15.3658 5.90245 15.3068C5.74538 15.1886 5.523 15.0162 5.25705 14.7957C4.72605 14.3554 4.01676 13.7195 3.3056 12.9379C1.90502 11.3984 0.399414 9.18674 0.399414 6.7273C0.399414 5.05686 1.03643 3.4502 2.17756 2.26191C3.31949 1.0728 4.87357 0.400024 6.49941 0.400024C8.12525 0.400024 9.67931 1.0728 10.8213 2.26191C11.9624 3.4502 12.5994 5.05686 12.5994 6.7273C12.5994 9.18674 11.0938 11.3984 9.69321 12.9379C8.98207 13.7195 8.27277 14.3554 7.74177 14.7957C7.47582 15.0162 7.25344 15.1886 7.09637 15.3068C7.01781 15.3658 6.9555 15.4114 6.91215 15.4427C6.89047 15.4583 6.87353 15.4704 6.86166 15.4788L6.84769 15.4886L6.84363 15.4914L6.84235 15.4923C6.84218 15.4924 6.84157 15.4929 6.49941 15ZM6.49941 15L6.84235 15.4923C6.6366 15.6352 6.36267 15.6355 6.15692 15.4926L6.49941 15ZM6.49941 5.41821C5.84093 5.41821 5.26608 5.98118 5.26608 6.7273C5.26608 7.47342 5.84093 8.03639 6.49941 8.03639C7.1579 8.03639 7.73275 7.47342 7.73275 6.7273C7.73275 5.98118 7.1579 5.41821 6.49941 5.41821ZM4.06608 6.7273C4.06608 5.36469 5.13285 4.21821 6.49941 4.21821C7.86597 4.21821 8.93275 5.36469 8.93275 6.7273C8.93275 8.0899 7.86597 9.23639 6.49941 9.23639C5.13285 9.23639 4.06608 8.0899 4.06608 6.7273Z"
													  fill="currentColor"/>
											</svg>
											<?php $listing->the_locations( true, true ); ?>
										</li>
									<?php endif; ?>
									<li>
										<svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd"
												  d="M1.68652 6.00002C1.75679 6.11959 1.85087 6.27339 1.96807 6.45162C2.26198 6.89859 2.69741 7.49325 3.26297 8.08573C4.40426 9.28133 6.00763 10.4 7.99941 10.4C9.9912 10.4 11.5946 9.28133 12.7358 8.08573C13.3014 7.49325 13.7368 6.89859 14.0307 6.45162C14.1479 6.27339 14.242 6.11959 14.3123 6.00002C14.242 5.88045 14.1479 5.72666 14.0307 5.54843C13.7368 5.10146 13.3014 4.5068 12.7358 3.91431C11.5946 2.71868 9.9912 1.60002 7.99941 1.60002C6.00763 1.60002 4.40426 2.71868 3.26297 3.91431C2.69741 4.5068 2.26198 5.10146 1.96807 5.54843C1.85087 5.72666 1.75679 5.88045 1.68652 6.00002ZM14.9994 6.00002C15.5341 5.72781 15.534 5.72761 15.5339 5.72739L15.5329 5.72542L15.5307 5.7212L15.5235 5.70742C15.5175 5.6959 15.509 5.67975 15.4979 5.65928C15.4759 5.61834 15.4438 5.56009 15.402 5.48699C15.3183 5.34086 15.195 5.13494 15.0334 4.88912C14.7108 4.39859 14.2315 3.74325 13.6039 3.08574C12.3588 1.78137 10.4622 0.400024 7.99941 0.400024C5.53665 0.400024 3.64002 1.78137 2.39494 3.08574C1.76732 3.74325 1.28798 4.39859 0.96542 4.88912C0.803772 5.13494 0.680525 5.34086 0.596835 5.48699C0.554968 5.56009 0.522933 5.61834 0.500878 5.65928C0.489848 5.67975 0.481309 5.6959 0.475277 5.70742L0.468111 5.7212L0.465942 5.72542L0.465211 5.72684C0.465097 5.72706 0.464716 5.72781 0.999414 6.00002L0.464716 5.72781C0.377647 5.89884 0.377647 6.1012 0.464716 6.27223L0.999414 6.00002C0.464716 6.27223 0.464602 6.27201 0.464716 6.27223L0.465211 6.2732L0.465942 6.27463L0.468111 6.27885L0.475277 6.29263C0.481309 6.30415 0.489848 6.3203 0.500878 6.34077C0.522933 6.3817 0.554968 6.43996 0.596835 6.51306C0.680525 6.65919 0.803772 6.86511 0.96542 7.11093C1.28798 7.60146 1.76732 8.25683 2.39494 8.91433C3.64002 10.2186 5.53665 11.6 7.99941 11.6C10.4622 11.6 12.3588 10.2186 13.6039 8.91433C14.2315 8.25683 14.7108 7.60146 15.0334 7.11093C15.195 6.86511 15.3183 6.65919 15.402 6.51306C15.4438 6.43996 15.4759 6.3817 15.4979 6.34077C15.509 6.3203 15.5175 6.30415 15.5235 6.29263L15.5307 6.27885L15.5329 6.27463L15.5336 6.2732C15.5337 6.27298 15.5341 6.27223 14.9994 6.00002ZM14.9994 6.00002L15.5341 6.27223C15.6212 6.1012 15.621 5.89842 15.5339 5.72739L14.9994 6.00002ZM7.99941 4.72502C7.26618 4.72502 6.69032 5.30601 6.69032 6.00002C6.69032 6.69404 7.26618 7.27502 7.99941 7.27502C8.73264 7.27502 9.3085 6.69404 9.3085 6.00002C9.3085 5.30601 8.73264 4.72502 7.99941 4.72502ZM5.49032 6.00002C5.49032 4.62297 6.62392 3.52502 7.99941 3.52502C9.37491 3.52502 10.5085 4.62297 10.5085 6.00002C10.5085 7.37707 9.37491 8.47503 7.99941 8.47503C6.62392 8.47503 5.49032 7.37707 5.49032 6.00002Z"
												  fill="currentColor"/>
										</svg>
										<?php echo sprintf( esc_html__( '%1$s Views', 'classified-listing' ),
											number_format_i18n( $listing->get_view_counts() ) ); ?>
									</li>
								</ul>
							</div>
							<span class="rtcl-my-listings-table-toggle-info">
								<span class="rtcl-icon rtcl-icon-angle-down"></span>
							</span>
							<?php do_action( 'rtcl_listing_loop_extra_meta', $listing ); ?>
						</td>
						<td class="price-cell list-on-responsive" data-column="<?php _e( 'Price:', 'classified-listing' ); ?>">
							<?php if( $listing->can_show_price() ) { Functions::print_html( $listing->get_price_html() ); } ?>
						</td>
						<td class="list-on-responsive" data-column="<?php _e( 'Expires On:', 'classified-listing' ); ?>">
							<?php
							if ( $listing->get_status() !== 'pending' ) {
								if ( get_post_meta( $listing->get_id(), 'never_expires', true ) ) {
									esc_html_e( 'Never Expires', 'classified-listing' );
								} else if ( $expiry_date = get_post_meta( $listing->get_id(), 'expiry_date', true ) ) {
									echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $expiry_date ) );
								}
							}
							?>
						</td>
						<td class="status-cell list-on-responsive" data-column="<?php _e( 'Status:', 'classified-listing' ); ?>">
							<span class="<?php echo esc_attr( strtolower( $post->post_status ) ); ?>">
								<?php echo Functions::get_status_i18n( $post->post_status ); ?>
							</span>
						</td>
						<td class="list-on-responsive" data-column="<?php _e( 'Action:', 'classified-listing' ); ?>">
							<?php if ( apply_filters( 'rtcl_my_listing_actions_button_display', true ) ) { ?>
								<div class="rtcl-actions-wrap">
									<span class="actions-dot">
										<svg width="18" height="4" viewBox="0 0 18 4" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="2" cy="2" r="2" fill="#646464"/>
											<circle cx="9" cy="2" r="2" fill="#646464"/>
											<circle cx="16" cy="2" r="2" fill="#646464"/>
										</svg>
									</span>
									<div class="rtcl-actions">
										<?php do_action( 'rtcl_my_listing_actions', $listing ); ?>
									</div>
								</div>
							<?php } ?>
						</td>
					</tr>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
				</tbody>
			</table>
		</div>
		<!-- pagination here -->
		<?php Pagination::pagination( $rtcl_query, true ); ?>
	<?php else: ?>
		<p class="rtcl-no-data-found"><?php esc_html_e( "No listing found.", 'classified-listing' ); ?></p>
	<?php endif; ?>

	<?php do_action( 'rtcl_my_account_after_my_listing', $rtcl_query ); ?>
</div>