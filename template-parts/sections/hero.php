<?php
/**
 * Hero section.
 *
 * Layouts: center (background image), split (media right), form (quote form
 * right) and compact (short band for inner pages).
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data   = $args['data'] ?? array();
$layout = $data['layout'] ?? 'center';
$image  = $data['image'] ?? 0;
$title  = $data['title'] ?? '';
$rating = $data['rating_text'] ?? '';
$score  = (float) ( $data['rating_value'] ?? 5 );
$b1     = packgens_link( $data['button_1'] ?? null );
$b2     = packgens_link( $data['button_2'] ?? null );
$bg     = ( in_array( $layout, array( 'center', 'form' ), true ) && $image ) ? packgens_image_url( $image, 'full' ) : '';

$features = array_filter(
	(array) ( $data['features'] ?? array() ),
	static function ( $feature ) {
		return ! empty( $feature['title'] );
	}
);

/*
 * The copy block is a slider when the repeater holds more than one slide.
 * With none, the single heading above becomes that one slide, so the markup
 * below has only one shape to deal with.
 */
$slides = array_values(
	array_filter(
		(array) ( $data['slides'] ?? array() ),
		static function ( $slide ) {
			return ! empty( $slide['title'] ) || ! empty( $slide['description'] );
		}
	)
);

if ( empty( $slides ) ) {
	$slides = array(
		array(
			'eyebrow'     => $data['eyebrow'] ?? '',
			'title'       => $title,
			'description' => $data['description'] ?? '',
			'button_1'    => $data['button_1'] ?? null,
			'button_2'    => $data['button_2'] ?? null,
		),
	);
}

$is_slider = count( $slides ) > 1;
$hero_id   = packgens_unique_id( 'pg-hero' );
?>
<section class="pg-hero pg-hero--<?php echo esc_attr( $layout ); ?><?php echo $features ? ' pg-hero--has-strip' : ''; ?>">

	<?php if ( $bg ) : ?>
		<div class="pg-hero__bg" role="presentation">
			<?php packgens_image( $image, 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ), '' ); ?>
		</div>
	<?php endif; ?>

	<div class="pg-container pg-hero__inner">

		<div class="pg-hero__copy">
			<?php if ( $rating ) : ?>
				<p class="pg-rating-pill">
					<?php packgens_stars( $score ); ?>
					<span><?php echo esc_html( $rating ); ?></span>
				</p>
			<?php endif; ?>

			<?php
			if ( $is_slider ) {
				packgens_carousel_open(
					$hero_id,
					array(
						'class'    => 'pg-hero__slider',
						'label'    => __( 'Highlights', 'packgens' ),
						'effect'   => 'fade',
						'autoplay' => 6000,
						'per_view' => array( 0 => 1 ),
					)
				);
			}

			foreach ( $slides as $slide_index => $slide ) :
				$s_b1 = packgens_link( $slide['button_1'] ?? null );
				$s_b2 = packgens_link( $slide['button_2'] ?? null );

				// Only the first headline is the page's h1; the rotating
				// alternates are styled text, so the page keeps one h1.
				$title_tag = 0 === $slide_index ? 'h1' : 'p';

				if ( $is_slider ) {
					echo '<div class="swiper-slide">';
				}
				?>
				<div class="pg-hero__slide">
					<?php if ( ! empty( $slide['eyebrow'] ) ) : ?>
						<p class="pg-hero__eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $slide['title'] ) ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="pg-hero__title"><?php echo esc_html( $slide['title'] ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>

					<?php if ( ! empty( $slide['description'] ) ) : ?>
						<p class="pg-hero__desc"><?php echo esc_html( $slide['description'] ); ?></p>
					<?php endif; ?>

					<?php if ( $s_b1 || $s_b2 ) : ?>
						<div class="pg-hero__actions">
							<?php
							if ( $s_b1 ) {
								packgens_button( $s_b1, 'white', '', '' );
							}

							if ( $s_b2 ) {
								packgens_button( $s_b2, 'outline-light', '', 'message-circle' );
							}
							?>
						</div>
					<?php endif; ?>
				</div>
				<?php
				if ( $is_slider ) {
					echo '</div>';
				}
			endforeach;

			if ( $is_slider ) {
				packgens_carousel_close( $hero_id, 'pg-carousel__nav--sides pg-carousel__nav--hero' );
			}
			?>
		</div>

		<?php if ( 'split' === $layout && $image ) : ?>
			<div class="pg-hero__media">
				<?php packgens_image( $image, 'packgens-wide', array( 'loading' => 'eager', 'fetchpriority' => 'high' ), '' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( 'form' === $layout ) : ?>
			<div class="pg-hero__form">
				<?php
				get_template_part(
					'template-parts/forms/quote',
					null,
					array(
						'variant' => 'panel',
						'labels'  => false,
						'title'   => packgens_global( 'quote_form', 'title', __( 'Get Instant Quote', 'packgens' ) ),
					)
				);
				?>
			</div>
		<?php endif; ?>

	</div>

	<?php if ( $features ) : ?>
		<div class="pg-container pg-hero__strip-wrap">
			<ul class="pg-hero__strip">
				<?php foreach ( $features as $feature ) : ?>
					<li class="pg-hero__feature">
						<span class="pg-usp__icon"><?php packgens_icon( $feature['icon'] ? $feature['icon'] : 'check-circle' ); ?></span>
						<span class="pg-usp__copy">
							<span class="pg-usp__title"><?php echo esc_html( $feature['title'] ); ?></span>
							<?php if ( ! empty( $feature['text'] ) ) : ?>
								<span class="pg-usp__text"><?php echo esc_html( $feature['text'] ); ?></span>
							<?php endif; ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</section>
