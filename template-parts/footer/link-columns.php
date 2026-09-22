<?php
/**
 * Footer link columns and contact details.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$columns = array(
	'footer_info'     => __( 'Information', 'packgens' ),
	'footer_industry' => __( 'Industries', 'packgens' ),
	'footer_products' => __( 'Products', 'packgens' ),
);

$btn1 = packgens_link( packgens_global( 'footer', 'button_1' ) );
$btn2 = packgens_link( packgens_global( 'footer', 'button_2' ) );

$whatsapp = packgens_get_option( 'whatsapp' );
$phone2   = packgens_get_option( 'phone_secondary', packgens_get_option( 'phone' ) );
$email    = packgens_get_option( 'email' );
$address  = packgens_get_option( 'address' );

// Drop locations with no menu assigned so the grid does not gain empty cells.
$columns = array_filter( $columns, 'has_nav_menu', ARRAY_FILTER_USE_KEY );

if ( empty( $columns ) ) {
	return;
}

$first_location = array_key_first( $columns );
?>
<?php foreach ( $columns as $location => $label ) : ?>
	<nav class="pg-footer__col" aria-label="<?php echo esc_attr( $label ); ?>">
		<h2 class="pg-footer__heading"><?php echo esc_html( $label ); ?></h2>

		<?php
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'pg-footer__menu',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		?>

		<?php if ( $location === $first_location && ( $btn1 || $btn2 ) ) : ?>
			<div class="pg-footer__actions">
				<?php
				if ( $btn1 ) {
					packgens_button( $btn1, 'outline-light', '', '' );
				}

				if ( $btn2 ) {
					packgens_button( $btn2, 'accent' );
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( 'footer_industry' === $location ) : ?>
			<div class="pg-footer__contact">
				<?php if ( $whatsapp ) : ?>
					<h3 class="pg-footer__contact-title"><?php packgens_the_label( 'footer', 'instant_info', __( 'Instant Information', 'packgens' ) ); ?></h3>
					<p>
						<a href="<?php echo esc_url( packgens_whatsapp_href( $whatsapp ) ); ?>" target="_blank" rel="noopener">
							<?php packgens_icon( 'whatsapp' ); ?><?php echo esc_html( $whatsapp ); ?>
						</a>
					</p>
				<?php endif; ?>

				<?php if ( $email ) : ?>
					<h3 class="pg-footer__contact-title"><?php packgens_the_label( 'footer', 'send_email', __( 'Send Email', 'packgens' ) ); ?></h3>
					<p>
						<a href="mailto:<?php echo esc_attr( $email ); ?>">
							<?php packgens_icon( 'mail' ); ?><?php echo esc_html( $email ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( 'footer_products' === $location ) : ?>
			<div class="pg-footer__contact">
				<?php if ( $phone2 ) : ?>
					<h3 class="pg-footer__contact-title"><?php packgens_the_label( 'footer', 'call_info', __( 'Call Information', 'packgens' ) ); ?></h3>
					<p>
						<a href="<?php echo esc_url( packgens_tel_href( $phone2 ) ); ?>">
							<?php packgens_icon( 'phone' ); ?><?php echo esc_html( $phone2 ); ?>
						</a>
					</p>
				<?php endif; ?>

				<?php if ( $address ) : ?>
					<h3 class="pg-footer__contact-title"><?php packgens_the_label( 'footer', 'address', __( 'Address', 'packgens' ) ); ?></h3>
					<p class="pg-footer__address">
						<?php packgens_icon( 'map-pin' ); ?>
						<span><?php echo nl2br( esc_html( $address ) ); ?></span>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</nav>
<?php endforeach; ?>
