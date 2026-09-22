<?php
/**
 * Template Name: FAQ
 *
 * Sticky quote card beside grouped FAQ accordions.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_the_ID();
$data    = packgens_field( 'faqs_section', $page_id, array() );
$groups  = array_values(
	array_filter(
		(array) ( $data['groups'] ?? array() ),
		static function ( $group ) {
			return ! empty( $group['items'] );
		}
	)
);

// Each group gets a stable id so the sidebar can link straight to it.
foreach ( $groups as $index => $group ) {
	$groups[ $index ]['anchor'] = ! empty( $group['title'] )
		? sanitize_title( $group['title'] )
		: 'faq-group-' . ( $index + 1 );
}

packgens_render_section( 'hero', $page_id, 'page' );

if ( packgens_field( 'hero_section', $page_id ) === null && empty( $groups ) ) {
	get_template_part( 'template-parts/components/page-header' );
}
?>

<section class="pg-section pg-section--faq-page">
	<div class="pg-container pg-faq-page">

		<aside class="pg-faq-page__aside">
			<?php if ( count( $groups ) > 1 ) : ?>
				<nav class="pg-faq-nav" aria-label="<?php esc_attr_e( 'Question groups', 'packgens' ); ?>">
					<span class="pg-faq-nav__label"><?php echo esc_html( $data['nav_label'] ?? __( 'Friendly ask question', 'packgens' ) ); ?></span>

					<ul class="pg-faq-nav__list">
						<?php foreach ( $groups as $group ) : ?>
							<?php if ( empty( $group['title'] ) ) : ?>
								<?php continue; ?>
							<?php endif; ?>
							<li>
								<a href="#<?php echo esc_attr( $group['anchor'] ); ?>"><?php echo esc_html( $group['title'] ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/components/quote-card' ); ?>
		</aside>

		<div class="pg-faq-page__main">
			<?php if ( empty( $groups ) ) : ?>
				<div class="pg-prose">
					<?php
					while ( have_posts() ) :
						the_post();
						the_content();
					endwhile;
					?>
				</div>
			<?php else : ?>
				<?php foreach ( $groups as $group_index => $group ) : ?>
					<div class="pg-faq-page__group" id="<?php echo esc_attr( $group['anchor'] ); ?>">
						<?php if ( ! empty( $group['title'] ) ) : ?>
							<h2 class="pg-faq-page__group-title"><?php echo esc_html( $group['title'] ); ?></h2>
						<?php endif; ?>

						<div class="pg-faq-page__panel">
							<div class="pg-accordion pg-accordion--flush" data-pg-accordion>
								<?php
								foreach ( array_values( (array) $group['items'] ) as $index => $item ) :
									if ( empty( $item['title'] ) ) {
										continue;
									}

									$panel_id   = packgens_unique_id( 'pg-faq-panel' );
									$trigger_id = $panel_id . '-trigger';
									// Every group leads with its first answer showing.
									$open       = ( 0 === $index );
									?>
									<div class="pg-accordion__item<?php echo $open ? ' is-open' : ''; ?>">
										<h3 class="pg-accordion__heading">
											<button type="button"
												class="pg-accordion__trigger"
												id="<?php echo esc_attr( $trigger_id ); ?>"
												aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"
												aria-controls="<?php echo esc_attr( $panel_id ); ?>">
												<span><?php echo esc_html( $item['title'] ); ?></span>
												<span class="pg-accordion__icon"><?php packgens_icon( $open ? 'minus' : 'plus' ); ?></span>
											</button>
										</h3>
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
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

	</div>
</section>

<?php
packgens_render_sections( 'page', $page_id, array(), array( 'hero', 'faqs' ) );

get_footer();
