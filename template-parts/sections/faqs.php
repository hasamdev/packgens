<?php
/**
 * FAQ section: one or more accordion groups.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data   = $args['data'] ?? array();
$groups = (array) ( $data['groups'] ?? array() );

// Keep only groups that actually contain questions.
$groups = array_values(
	array_filter(
		$groups,
		static function ( $group ) {
			return ! empty( $group['items'] ) && is_array( $group['items'] );
		}
	)
);

if ( empty( $groups ) ) {
	return;
}

$multi = count( $groups ) > 1;

// With several groups each gets an h3 title, so the questions sit at h4.
// With a single group the questions follow the section h2 directly.
$question_tag = $multi ? 'h4' : 'h3';
?>
<section class="<?php echo esc_attr( packgens_section_class( 'faqs' ) ); ?>">
	<div class="pg-container">

		<?php packgens_section_header( $data, array( 'align' => 'center', 'button' => false ) ); ?>

		<div class="pg-faqs<?php echo $multi ? ' pg-faqs--grouped' : ''; ?>">
			<?php foreach ( $groups as $group_index => $group ) : ?>
				<div class="pg-faqs__group">

					<?php if ( $multi && ! empty( $group['title'] ) ) : ?>
						<h3 class="pg-faqs__group-title"><?php echo esc_html( $group['title'] ); ?></h3>
					<?php endif; ?>

					<?php if ( $multi && ! empty( $group['subtitle'] ) ) : ?>
						<p class="pg-faqs__group-sub"><?php echo esc_html( $group['subtitle'] ); ?></p>
					<?php endif; ?>

					<div class="pg-accordion" data-pg-accordion>
						<?php
						foreach ( array_values( (array) $group['items'] ) as $index => $item ) :
							if ( empty( $item['title'] ) ) {
								continue;
							}

							$panel_id   = packgens_unique_id( 'pg-faq-panel' );
							$trigger_id = $panel_id . '-trigger';
							$open       = ( 0 === $group_index && 0 === $index );
							?>
							<div class="pg-accordion__item<?php echo $open ? ' is-open' : ''; ?>">
								<<?php echo esc_attr( $question_tag ); ?> class="pg-accordion__heading">
									<button type="button"
										class="pg-accordion__trigger"
										id="<?php echo esc_attr( $trigger_id ); ?>"
										aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"
										aria-controls="<?php echo esc_attr( $panel_id ); ?>">
										<span><?php echo esc_html( $item['title'] ); ?></span>
										<span class="pg-accordion__icon"><?php packgens_icon( $open ? 'minus' : 'plus' ); ?></span>
									</button>
								</<?php echo esc_attr( $question_tag ); ?>>
								<div class="pg-accordion__panel"
									id="<?php echo esc_attr( $panel_id ); ?>"
									role="region"
									aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>">
									<div>
										<div class="pg-accordion__content">
											<?php echo wp_kses_post( wpautop( $item['description'] ?? '' ) ); ?>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $data['button'] ) ) : ?>
			<div class="pg-section__footer">
				<?php packgens_button( $data['button'], 'primary' ); ?>
			</div>
		<?php endif; ?>

	</div>
</section>
