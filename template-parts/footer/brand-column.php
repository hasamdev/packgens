<?php
/**
 * Footer first column: logo, newsletter and social profiles.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$about      = packgens_global( 'footer', 'about' );
$news_title = packgens_global( 'footer', 'newsletter_title' );
$news_text  = packgens_global( 'footer', 'newsletter_text' );
?>
<div class="pg-footer__brand-col">

	<div class="pg-footer__logo">
		<?php get_template_part( 'template-parts/header/branding' ); ?>
	</div>

	<?php if ( $about ) : ?>
		<p class="pg-footer__about"><?php echo esc_html( $about ); ?></p>
	<?php endif; ?>

	<?php if ( $news_title || $news_text ) : ?>
		<div class="pg-newsletter">
			<?php if ( $news_title ) : ?>
				<h2 class="pg-newsletter__title">
					<?php
					// A span is allowed so the offer can carry the accent colour.
					echo wp_kses( $news_title, array( 'span' => array( 'class' => array() ), 'br' => array() ) );
					?>
				</h2>
			<?php endif; ?>

			<?php if ( $news_text ) : ?>
				<p class="pg-newsletter__text"><?php echo esc_html( $news_text ); ?></p>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/forms/newsletter' ); ?>
		</div>
	<?php endif; ?>

</div>
