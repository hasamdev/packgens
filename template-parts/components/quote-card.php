<?php
/**
 * Compact "get a quote" card used in sidebars.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$title = $args['title'] ?? packgens_label( 'quote_card', 'title', __( 'Ready to box? Let&rsquo;s get started!', 'packgens' ) );
$text  = $args['text'] ?? packgens_label( 'quote_card_text', 'text', __( 'Get in touch with a Packgens custom packaging specialist for an instant price quote.', 'packgens' ) );
$phone = packgens_get_option( 'phone' );
$quote = packgens_get_option( 'quote_page_url' );
?>
<div class="pg-quote-card">
	<h2 class="pg-quote-card__title"><?php echo wp_kses_post( $title ); ?></h2>
	<p class="pg-quote-card__text"><?php echo esc_html( $text ); ?></p>

	<?php if ( $phone ) : ?>
		<a class="pg-quote-card__phone" href="<?php echo esc_url( packgens_tel_href( $phone ) ); ?>">
			<?php packgens_icon( 'phone' ); ?>
			<span>
				<span class="pg-quote-card__phone-label"><?php packgens_the_label( 'quote_card', 'phone_label', __( 'Call Us Toll Free', 'packgens' ) ); ?></span>
				<span class="pg-quote-card__phone-number"><?php echo esc_html( $phone ); ?></span>
			</span>
		</a>
	<?php endif; ?>

	<?php if ( $quote ) : ?>
		<a class="pg-btn pg-btn--white" href="<?php echo esc_url( $quote ); ?>">
			<?php packgens_the_label( 'quote_card', 'button', __( 'Get a Free Quote', 'packgens' ) ); ?>
			<?php packgens_icon( 'chevrons-right', 'pg-btn__icon' ); ?>
		</a>
	<?php endif; ?>
</div>
