<?php
/**
 * About section: two flanking images with copy in the middle.
 *
 * The left image carries a review-platform badge above it and the right one a
 * small social-proof pill below, so the section reads as a claim with evidence
 * either side of it.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data   = $args['data'] ?? array();
$left   = $data['image_left'] ?? 0;
$right  = $data['image_right'] ?? 0;
$points = array_filter( (array) ( $data['points'] ?? array() ), static function ( $p ) {
	return ! empty( $p['text'] );
} );

$rating_logo  = $data['rating_logo'] ?? 0;
$rating_score = trim( (string) ( $data['rating_score'] ?? '' ) );
$rating_link  = packgens_link( $data['rating_link'] ?? null );
$has_rating   = $rating_logo || $rating_score || $rating_link;

$avatars = array_values(
	array_filter(
		(array) ( $data['proof_avatars'] ?? array() ),
		static function ( $avatar ) {
			return ! empty( $avatar['image'] );
		}
	)
);

$proof_text   = trim( (string) ( $data['proof_text'] ?? '' ) );
$proof_rating = (float) ( $data['proof_rating'] ?? 0 );
$has_proof    = $avatars || $proof_text;
?>
<?php
// With artwork on one side only the block is a two-column split, and the copy
// reads better ranged left than centred.
$about_modifier = ( $left && $right ) ? '' : ' pg-about--split';
?>
<section class="<?php echo esc_attr( packgens_section_class( 'about' ) ); ?>">
	<div class="pg-container pg-about<?php echo esc_attr( $about_modifier ); ?>">

		<?php if ( $left || $has_rating ) : ?>
			<div class="pg-about__media pg-about__media--left">
				<?php if ( $has_rating ) : ?>
					<div class="pg-about__rating">
						<?php if ( $rating_logo ) : ?>
							<span class="pg-about__rating-logo">
								<?php packgens_image( $rating_logo, 'medium', array(), '' ); ?>
							</span>
						<?php endif; ?>

						<?php if ( $rating_score || $rating_link ) : ?>
							<p class="pg-about__rating-meta">
								<?php if ( $rating_score ) : ?>
									<span><?php echo esc_html( $rating_score ); ?></span>
								<?php endif; ?>

								<?php if ( $rating_link ) : ?>
									<a href="<?php echo esc_url( $rating_link['url'] ); ?>"<?php echo $rating_link['target'] ? ' target="_blank" rel="noopener"' : ''; ?>>
										<?php echo esc_html( $rating_link['title'] ); ?>
									</a>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $left ) : ?>
					<figure class="pg-about__figure pg-about__figure--tall">
						<?php packgens_image( $left, 'packgens-card-tall', array(), '' ); ?>
					</figure>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="pg-about__copy">
			<?php if ( ! empty( $data['eyebrow'] ) ) : ?>
				<span class="pg-badge pg-badge--soft"><?php echo esc_html( $data['eyebrow'] ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $data['title'] ) ) : ?>
				<h2 class="pg-about__title"><?php echo esc_html( $data['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $data['description'] ) ) : ?>
				<p class="pg-about__lead"><?php echo esc_html( $data['description'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $data['body'] ) ) : ?>
				<div class="pg-prose"><?php echo wp_kses_post( $data['body'] ); ?></div>
			<?php endif; ?>

			<?php if ( $points ) : ?>
				<ul class="pg-checklist">
					<?php foreach ( $points as $point ) : ?>
						<li>
							<?php packgens_icon( 'check-circle' ); ?>
							<span><?php echo esc_html( $point['text'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php
			$contact_phone = trim( (string) ( $data['contact_phone'] ?? '' ) );

			if ( ! empty( $data['button'] ) || $contact_phone ) :
				?>
				<div class="pg-about__actions">
					<?php if ( ! empty( $data['button'] ) ) : ?>
						<?php packgens_button( $data['button'], 'primary', '', 'arrow-right' ); ?>
					<?php endif; ?>

					<?php if ( $contact_phone ) : ?>
						<a class="pg-about__direct" href="<?php echo esc_url( packgens_tel_href( $contact_phone ) ); ?>">
							<span class="pg-about__direct-copy">
								<?php if ( ! empty( $data['contact_label'] ) ) : ?>
									<span class="pg-about__direct-label"><?php echo esc_html( $data['contact_label'] ); ?></span>
								<?php endif; ?>
								<span class="pg-about__direct-number"><?php echo esc_html( $contact_phone ); ?></span>
							</span>

							<?php if ( ! empty( $data['contact_photo'] ) ) : ?>
								<span class="pg-about__direct-photo">
									<?php packgens_image( $data['contact_photo'], 'packgens-thumb', array(), '' ); ?>
								</span>
							<?php endif; ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $right || $has_proof ) : ?>
			<div class="pg-about__media pg-about__media--right">
				<?php if ( $right ) : ?>
					<figure class="pg-about__figure pg-about__figure--wide">
						<?php packgens_image( $right, 'packgens-card', array(), '' ); ?>
					</figure>
				<?php endif; ?>

				<?php if ( $has_proof ) : ?>
					<div class="pg-about__proof">
						<?php if ( $avatars ) : ?>
							<span class="pg-avatars">
								<?php foreach ( $avatars as $avatar ) : ?>
									<?php packgens_image( $avatar['image'], 'packgens-thumb', array(), '' ); ?>
								<?php endforeach; ?>
							</span>
						<?php endif; ?>

						<span class="pg-about__proof-body">
							<?php if ( $proof_rating > 0 ) : ?>
								<?php packgens_stars( $proof_rating ); ?>
							<?php endif; ?>

							<?php if ( $proof_text ) : ?>
								<span class="pg-about__proof-text"><?php echo esc_html( $proof_text ); ?></span>
							<?php endif; ?>
						</span>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
