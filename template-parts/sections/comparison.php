<?php
/**
 * Comparison section: a feature table beside a promo card, on a dark band.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$rows  = array_filter( (array) ( $data['rows'] ?? array() ), static function ( $row ) {
	return ! empty( $row['text'] );
} );
$points = array_filter( (array) ( $data['points'] ?? array() ), static function ( $p ) {
	return ! empty( $p['text'] );
} );
$promo = $data['promo'] ?? array();

if ( empty( $rows ) ) {
	return;
}

$us_label   = $data['us_label'] ?? __( 'Us', 'packgens' );
$them_label = $data['them_label'] ?? __( 'Others', 'packgens' );
?>
<section class="<?php echo esc_attr( packgens_section_class( 'comparison', 'navy' ) ); ?>">
	<div class="pg-container">

		<?php packgens_section_header(
			array_diff_key( $data, array( 'eyebrow' => '' ) ),
			array( 'align' => 'center', 'button' => false )
		); ?>

		<div class="pg-comparison">

			<div class="pg-comparison__panel">
				<?php if ( ! empty( $data['eyebrow'] ) ) : ?>
					<span class="pg-badge pg-badge--dark"><?php echo esc_html( $data['eyebrow'] ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $data['table_heading'] ) ) : ?>
					<h3 class="pg-comparison__heading"><?php echo esc_html( $data['table_heading'] ); ?></h3>
				<?php endif; ?>

				<div class="pg-scroll-x">
					<table class="pg-comparison__table">
						<caption class="pg-sr-only"><?php echo esc_html( $data['title'] ?? __( 'Feature comparison', 'packgens' ) ); ?></caption>
						<thead>
							<tr>
								<th scope="col"><span class="pg-sr-only"><?php esc_html_e( 'Feature', 'packgens' ); ?></span></th>
								<th scope="col" class="pg-comparison__col pg-comparison__col--us"><?php echo esc_html( $us_label ); ?></th>
								<th scope="col" class="pg-comparison__col"><?php echo esc_html( $them_label ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rows as $row ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $row['text'] ); ?></th>
									<td class="pg-comparison__col--us">
										<?php packgens_comparison_mark( ! empty( $row['us'] ) ); ?>
									</td>
									<td>
										<?php packgens_comparison_mark( ! empty( $row['them'] ) ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>

			<?php if ( ! empty( $promo['image'] ) || ! empty( $promo['title'] ) ) : ?>
				<aside class="pg-comparison__promo">
					<span class="pg-comparison__promo-mark"><?php packgens_icon( 'brand-tile' ); ?></span>

					<?php if ( ! empty( $promo['image'] ) ) : ?>
						<div class="pg-comparison__promo-media">
							<?php packgens_image( $promo['image'], 'packgens-card', array(), '' ); ?>
						</div>
					<?php endif; ?>

					<div class="pg-comparison__promo-copy">
						<?php if ( ! empty( $promo['title'] ) ) : ?>
							<p class="pg-comparison__promo-title">
								<?php
								// A span is allowed so one word can carry the accent colour.
								echo wp_kses( $promo['title'], array( 'span' => array( 'class' => array() ), 'br' => array() ) );
								?>
							</p>
						<?php endif; ?>

						<?php if ( ! empty( $promo['button'] ) ) : ?>
							<?php packgens_button( $promo['button'], 'white' ); ?>
						<?php endif; ?>
					</div>
				</aside>
			<?php endif; ?>

		</div>

		<?php if ( $points ) : ?>
			<ul class="pg-comparison__points">
				<?php foreach ( $points as $point ) : ?>
					<li>
						<?php packgens_icon( 'check-circle' ); ?>
						<span><?php echo esc_html( $point['text'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

	</div>
</section>
