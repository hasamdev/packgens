<?php
/**
 * Template Name: Industries
 *
 * Grid of every industry rather than the homepage carousel.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_the_ID();
$data    = packgens_field( 'industries_section', $page_id, array() );
$items   = array_filter(
	(array) ( $data['items'] ?? array() ),
	static function ( $item ) {
		return ! empty( $item['title'] ) || ! empty( $item['image'] );
	}
);

packgens_render_section( 'hero', $page_id, 'page' );

if ( ! packgens_field( 'hero_section', $page_id ) ) {
	get_template_part( 'template-parts/components/page-header' );
}
?>

<?php if ( $items ) : ?>
	<?php // A light band rather than the homepage green: the card bodies are white
	// and need something behind them, but the page keeps its own colour. ?>
	<section class="<?php echo esc_attr( packgens_section_class( 'industries-grid', 'gray-cool' ) ); ?>">
		<div class="pg-container">

			<?php packgens_section_header( $data, array( 'align' => 'center', 'button' => false ) ); ?>

			<div class="pg-grid" style="--pg-cols:1;--pg-cols-sm:2;--pg-cols-lg:3;--pg-cols-xl:4">
				<?php foreach ( $items as $item ) : ?>
					<?php get_template_part( 'template-parts/cards/industry', null, array( 'item' => $item ) ); ?>
				<?php endforeach; ?>
			</div>

		</div>
	</section>
<?php endif; ?>

<?php
if ( trim( (string) get_post_field( 'post_content', $page_id ) ) ) :
	?>
	<section class="pg-section pg-section--page-content">
		<div class="pg-container pg-container--narrow">
			<div class="pg-prose">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</div>
	</section>
	<?php
endif;

packgens_render_sections( 'page', $page_id, array(), array( 'hero', 'industries' ) );

get_footer();
