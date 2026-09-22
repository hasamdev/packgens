<?php
/**
 * Reviews section.
 *
 * Two rows of testimonial cards on the brand band, the second offset by half a
 * card, both driven by one pair of arrows.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data    = $args['data'] ?? array();
$reviews = packgens_get_section_reviews( $data, 16 );

if ( empty( $reviews ) ) {
	return;
}

$badges = array_filter( (array) ( $data['badges'] ?? array() ), static function ( $badge ) {
	return ! empty( $badge['name'] );
} );

// Alternate down the list so both rows carry a mix rather than one long half.
$rows = array( array(), array() );

foreach ( array_values( $reviews ) as $index => $review_id ) {
	$rows[ $index % 2 ][] = $review_id;
}

// One row is enough when there are only a handful of testimonials.
if ( count( $reviews ) < 4 ) {
	$rows = array( array_values( $reviews ) );
}

$group = packgens_unique_id( 'pg-reviews' );
// The catalogue draws this band on light grey; elsewhere it is the brand blue.
$is_light = 'product_cat' === ( $args['view'] ?? '' );
$surface  = $is_light ? 'gray' : 'blue';
// A white pill disappears on the light band, so the action reads in ink there.
$button_variant = $is_light ? 'dark' : 'white';
?>
<section class="<?php echo esc_attr( packgens_section_class( 'reviews', $surface ) ); ?>">
	<div class="pg-container">

		<div class="pg-reviews__head">
			<div class="pg-section-head__copy">
				<?php if ( ! empty( $data['title'] ) ) : ?>
					<h2 class="pg-section-head__title"><?php echo esc_html( $data['title'] ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $data['description'] ) ) : ?>
					<p class="pg-section-head__desc"><?php echo esc_html( $data['description'] ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $badges ) : ?>
				<ul class="pg-review-badges">
					<?php
					foreach ( $badges as $badge ) :
						$link = packgens_link( $badge['link'] ?? null );
						$tag  = $link ? 'a' : 'div';
						?>
						<li>
							<<?php echo esc_attr( $tag ); ?> class="pg-review-badge" data-pg-platform="<?php echo esc_attr( $badge['icon'] ?? '' ); ?>"<?php echo $link ? ' href="' . esc_url( $link['url'] ) . '" target="_blank" rel="noopener"' : ''; ?>>
								<span class="pg-review-badge__brand">
									<?php if ( ! empty( $badge['icon'] ) ) : ?>
										<?php packgens_icon( $badge['icon'] ); ?>
									<?php endif; ?>
									<span class="pg-review-badge__name"><?php echo esc_html( $badge['name'] ); ?></span>

									<span class="pg-review-badge__stars">
										<?php packgens_stars( 5 ); ?>
									</span>
								</span>

								<span class="pg-review-badge__meta">
									<?php if ( ! empty( $badge['score'] ) ) : ?>
										<span class="pg-review-badge__score"><?php echo esc_html( $badge['score'] ); ?><?php packgens_icon( 'star' ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $badge['count'] ) ) : ?>
										<span class="pg-review-badge__count"><?php echo esc_html( $badge['count'] ); ?></span>
									<?php endif; ?>
									<?php if ( $link ) : ?>
										<?php packgens_icon( 'arrow-right', 'pg-review-badge__arrow' ); ?>
									<?php endif; ?>
								</span>
							</<?php echo esc_attr( $tag ); ?>>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="pg-reviews__rows">
			<?php
			foreach ( $rows as $index => $row ) :
				if ( empty( $row ) ) {
					continue;
				}

				packgens_carousel_open(
					$group . '-row-' . ( $index + 1 ),
					array(
						'class'    => 'pg-reviews__track',
						'bleed'    => true,
						'group'    => $group,
						'stagger'  => 1 === $index,
						'auto'     => 0 === $index ? 'forward' : 'reverse',
						/* translators: %d: row number. */
						'label'    => sprintf( __( 'Customer reviews, row %d', 'packgens' ), $index + 1 ),
						'per_view' => array( 0 => 1.15, 576 => 1.7, 768 => 2.3, 992 => 3.1, 1400 => 3.5 ),
					)
				);

				foreach ( $row as $review_id ) {
					packgens_carousel_slide( 'template-parts/cards/review', null, array( 'id' => $review_id ) );
				}

				packgens_carousel_close();
			endforeach;
			?>
		</div>

		<div class="pg-reviews__foot">
			<?php packgens_carousel_nav( $group ); ?>

			<?php if ( ! empty( $data['button'] ) ) : ?>
				<?php packgens_button( $data['button'], $button_variant, __( 'View all reviews', 'packgens' ), 'arrow-right' ); ?>
			<?php endif; ?>
		</div>

	</div>
</section>
