<?php
/**
 * Product reviews: count, summary, search/filter/sort toolbar, the review list
 * and the review form.
 *
 * Overrides woocommerce/templates/single-product-reviews.php. Every review is
 * printed; product.js adds the toolbar behaviour and pages the list, so with
 * scripts off the visitor still gets every review and an open form.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() && ! $product->get_review_count() ) {
	return;
}

$product_id = $product->get_id();
$reviews    = packgens_product_reviews( $product_id );
$count      = count( array_filter( $reviews, static fn( $review ) => ! $review['pending'] ) );
$average    = (float) $product->get_average_rating();
$summary    = packgens_group_field( 'product_details', 'review_summary', $product_id );
$ratings_on = wc_review_ratings_enabled();
$can_review = comments_open()
	&& ( 'no' === get_option( 'woocommerce_review_rating_verification_required' ) || wc_customer_bought_product( '', get_current_user_id(), $product_id ) );

$title = $count
	? sprintf(
		/* translators: %s: number of reviews. */
		1 === $count
			? packgens_label( 'reviews', 'title_one', __( 'Reviewed by %s customer', 'packgens' ) )
			: packgens_label( 'reviews', 'title_many', __( 'Reviewed by %s customers', 'packgens' ) ),
		number_format_i18n( $count )
	)
	: packgens_label( 'reviews', 'title_empty', __( 'Customer reviews', 'packgens' ) );
?>
<div id="reviews" class="pg-product-reviews" data-pg-product-reviews
	data-per-page="5"
	data-range="<?php echo esc_attr( packgens_label( 'reviews', 'range', __( 'Displaying Reviews %1$s–%2$s of %3$s', 'packgens' ) ) ); ?>"
	data-page-label="<?php echo esc_attr( packgens_label( 'reviews', 'page', __( 'Go to page %s', 'packgens' ) ) ); ?>">

	<div class="pg-product-reviews__head">
		<div class="pg-product-reviews__heading">
			<h2 class="pg-product-reviews__title"><?php echo esc_html( $title ); ?></h2>

			<?php if ( $count && $ratings_on && $average > 0 ) : ?>
				<p class="pg-product-reviews__average">
					<?php packgens_stars( $average ); ?>
					<span>
						<?php
						printf(
							/* translators: %s: average rating. */
							esc_html( packgens_label( 'reviews', 'average', __( '%s out of 5', 'packgens' ) ) ),
							esc_html( number_format_i18n( $average, 1 ) )
						);
						?>
					</span>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( $can_review && $reviews ) : ?>
			<button type="button" class="pg-btn pg-btn--outline pg-product-reviews__write" data-pg-product-review-toggle aria-controls="review_form_wrapper" aria-expanded="true" hidden>
				<?php packgens_icon( 'pencil' ); ?>
				<span><?php echo esc_html( packgens_label( 'reviews', 'write', __( 'Write a review', 'packgens' ) ) ); ?></span>
			</button>
		<?php endif; ?>
	</div>

	<?php if ( $summary ) : ?>
		<div class="pg-product-reviews__summary">
			<h3 class="pg-product-reviews__summary-title">
				<?php packgens_icon( 'sparkles' ); ?>
				<?php echo esc_html( packgens_label( 'reviews', 'summary', __( 'Summary', 'packgens' ) ) ); ?>
			</h3>
			<div class="pg-product-reviews__summary-text"><?php echo wp_kses_post( wpautop( $summary ) ); ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $reviews ) : ?>
		<div class="pg-product-reviews__toolbar" data-pg-product-reviews-toolbar hidden>
			<label class="pg-product-reviews__search">
				<span class="pg-sr-only"><?php echo esc_html( packgens_label( 'reviews', 'search', __( 'Search reviews', 'packgens' ) ) ); ?></span>
				<input type="search" class="pg-product-reviews__search-input" data-pg-product-reviews-search
					placeholder="<?php echo esc_attr( packgens_label( 'reviews', 'search', __( 'Search reviews', 'packgens' ) ) ); ?>" />
				<?php packgens_icon( 'search' ); ?>
			</label>

			<?php if ( $ratings_on ) : ?>
				<label class="pg-product-reviews__select">
					<span class="pg-product-reviews__select-label"><?php echo esc_html( packgens_label( 'reviews', 'filter', __( 'Filter By', 'packgens' ) ) ); ?></span>
					<select data-pg-product-reviews-filter>
						<option value=""><?php echo esc_html( packgens_label( 'reviews', 'filter_all', __( 'All ratings', 'packgens' ) ) ); ?></option>
						<?php for ( $stars = 5; $stars >= 1; $stars-- ) : ?>
							<option value="<?php echo esc_attr( $stars ); ?>">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: number of stars. */
										1 === $stars
											? packgens_label( 'reviews', 'filter_star', __( '%s Star', 'packgens' ) )
											: packgens_label( 'reviews', 'filter_stars', __( '%s Stars', 'packgens' ) ),
										$stars
									)
								);
								?>
							</option>
						<?php endfor; ?>
					</select>
				</label>
			<?php endif; ?>

			<label class="pg-product-reviews__select">
				<span class="pg-product-reviews__select-label"><?php echo esc_html( packgens_label( 'reviews', 'sort', __( 'Sort by', 'packgens' ) ) ); ?></span>
				<select data-pg-product-reviews-sort>
					<option value="recent"><?php echo esc_html( packgens_label( 'reviews', 'sort_recent', __( 'Most Recent', 'packgens' ) ) ); ?></option>
					<?php if ( $ratings_on ) : ?>
						<option value="high"><?php echo esc_html( packgens_label( 'reviews', 'sort_high', __( 'Highest Rated', 'packgens' ) ) ); ?></option>
						<option value="low"><?php echo esc_html( packgens_label( 'reviews', 'sort_low', __( 'Lowest Rated', 'packgens' ) ) ); ?></option>
					<?php endif; ?>
				</select>
			</label>
		</div>

		<ol class="pg-product-reviews__list" data-pg-product-reviews-list>
			<?php foreach ( $reviews as $review ) : ?>
				<li class="pg-product-review" id="comment-<?php echo esc_attr( $review['id'] ); ?>"
					data-pg-product-review
					data-rating="<?php echo esc_attr( $review['rating'] ); ?>"
					data-time="<?php echo esc_attr( $review['time'] ); ?>">

					<?php if ( $ratings_on && $review['rating'] ) : ?>
						<p class="pg-product-review__rating">
							<?php packgens_stars( $review['rating'] ); ?>
							<span class="pg-product-review__score" aria-hidden="true"><?php echo esc_html( $review['rating'] ); ?></span>
						</p>
					<?php endif; ?>

					<?php if ( $review['title'] ) : ?>
						<h3 class="pg-product-review__title"><?php echo esc_html( $review['title'] ); ?></h3>
					<?php endif; ?>

					<p class="pg-product-review__meta">
						<time datetime="<?php echo esc_attr( gmdate( 'c', $review['time'] ) ); ?>"><?php echo esc_html( $review['date'] ); ?></time>
						<span><?php echo esc_html( $review['author'] ); ?></span>
						<?php if ( $review['location'] ) : ?>
							<span><?php echo esc_html( $review['location'] ); ?></span>
						<?php endif; ?>
						<?php if ( $review['verified'] ) : ?>
							<span class="pg-product-review__verified">
								<?php packgens_icon( 'check-circle' ); ?>
								<?php echo esc_html( packgens_label( 'reviews', 'verified', __( 'Verified Buyer', 'packgens' ) ) ); ?>
							</span>
						<?php endif; ?>
					</p>

					<?php if ( $review['pending'] ) : ?>
						<p class="pg-product-review__pending"><?php echo esc_html( packgens_label( 'reviews', 'pending', __( 'Your review is awaiting approval.', 'packgens' ) ) ); ?></p>
					<?php endif; ?>

					<div class="pg-product-review__text"><?php echo wp_kses_post( wpautop( $review['text'] ) ); ?></div>

					<?php foreach ( $review['replies'] as $reply ) : ?>
						<div class="pg-product-review__reply">
							<p class="pg-product-review__reply-by">
								<?php
								printf(
									/* translators: %s: name of whoever replied. */
									esc_html( packgens_label( 'reviews', 'reply_by', __( 'Response from %s', 'packgens' ) ) ),
									esc_html( $reply->comment_author )
								);
								?>
								<time datetime="<?php echo esc_attr( get_comment_date( 'c', $reply ) ); ?>"><?php echo esc_html( get_comment_date( 'M j, Y', $reply ) ); ?></time>
							</p>
							<?php echo wp_kses_post( wpautop( $reply->comment_content ) ); ?>
						</div>
					<?php endforeach; ?>
				</li>
			<?php endforeach; ?>
		</ol>

		<p class="pg-product-reviews__none" data-pg-product-reviews-none hidden><?php echo esc_html( packgens_label( 'reviews', 'no_match', __( 'No reviews match your search.', 'packgens' ) ) ); ?></p>

		<div class="pg-product-reviews__foot" data-pg-product-reviews-foot hidden>
			<p class="pg-product-reviews__range" data-pg-product-reviews-range aria-live="polite"></p>
			<nav class="pg-product-reviews__pages" data-pg-product-reviews-pages aria-label="<?php echo esc_attr( packgens_label( 'reviews', 'pages', __( 'Review pages', 'packgens' ) ) ); ?>"></nav>
		</div>
	<?php else : ?>
		<p class="pg-product-reviews__none"><?php echo esc_html( packgens_label( 'reviews', 'empty', __( 'There are no reviews yet.', 'packgens' ) ) ); ?></p>
	<?php endif; ?>

	<?php if ( $can_review ) : ?>
		<div id="review_form_wrapper" class="pg-product-review-form" data-pg-product-review-form>
			<div id="review_form">
				<?php comment_form( packgens_review_form_args( $product, (bool) $reviews ) ); ?>
			</div>
		</div>
	<?php elseif ( comments_open() ) : ?>
		<p class="pg-product-reviews__none"><?php echo esc_html( packgens_label( 'reviews', 'verified_only', __( 'Only logged in customers who have purchased this product may leave a review.', 'packgens' ) ) ); ?></p>
	<?php endif; ?>

</div>
