<?php
/**
 * Materials section: tabbed groups of material cards.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data   = $args['data'] ?? array();
$groups = packgens_get_section_materials( $data );

if ( empty( $groups ) ) {
	return;
}

$tabs_id = packgens_unique_id( 'pg-materials' );
?>
<section class="<?php echo esc_attr( packgens_section_class( 'materials', 'blue' ) ); ?>">
	<div class="pg-container">

		<div class="pg-materials__head">
			<div class="pg-section-head__copy">
				<?php if ( ! empty( $data['title'] ) ) : ?>
					<h2 class="pg-section-head__title"><?php echo esc_html( $data['title'] ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $data['description'] ) ) : ?>
					<p class="pg-section-head__desc"><?php echo esc_html( $data['description'] ); ?></p>
				<?php endif; ?>
			</div>

			<div class="pg-tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Material groups', 'packgens' ); ?>">
				<?php
				foreach ( $groups as $index => $group ) :
					$tab_icon = packgens_field( 'icon', 'term_' . $group['term']->term_id );
					?>
					<button type="button"
						class="pg-tabs__tab"
						role="tab"
						id="<?php echo esc_attr( $tabs_id . '-tab-' . $index ); ?>"
						aria-controls="<?php echo esc_attr( $tabs_id . '-panel-' . $index ); ?>"
						aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
						tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>">
						<?php if ( $tab_icon ) : ?>
							<?php packgens_icon( $tab_icon, 'pg-tabs__icon' ); ?>
						<?php endif; ?>
						<?php echo esc_html( $group['term']->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="pg-tabs" data-pg-tabs>
			<?php foreach ( $groups as $index => $group ) : ?>
				<div class="pg-tabs__panel"
					role="tabpanel"
					id="<?php echo esc_attr( $tabs_id . '-panel-' . $index ); ?>"
					aria-labelledby="<?php echo esc_attr( $tabs_id . '-tab-' . $index ); ?>"
					tabindex="0"
					<?php echo 0 === $index ? '' : 'hidden'; ?>>
					<div class="pg-grid" style="--pg-cols:1;--pg-cols-sm:2;--pg-cols-lg:4">
						<?php foreach ( $group['posts'] as $material_id ) : ?>
							<?php get_template_part( 'template-parts/cards/material', null, array( 'id' => $material_id ) ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $data['button'] ) ) : ?>
			<div class="pg-section__footer">
				<?php packgens_button( $data['button'], 'white' ); ?>
			</div>
		<?php endif; ?>

	</div>
</section>
