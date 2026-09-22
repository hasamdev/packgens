<?php
/**
 * Announcement bar above the header card.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

if ( ! packgens_get_option( 'topbar_enabled', true ) ) {
	return;
}

$message    = packgens_get_option( 'topbar_message' );
$chat_label = packgens_get_option( 'topbar_chat_label' );
$chat_url   = packgens_get_option( 'topbar_chat_url' );

if ( ! $message && ! $chat_label ) {
	return;
}
?>
<div class="pg-topbar">
	<div class="pg-container pg-topbar__inner">

		<?php if ( $message ) : ?>
			<p class="pg-topbar__message"><?php echo esc_html( $message ); ?></p>
		<?php endif; ?>

		<?php if ( $chat_label ) : ?>
			<p class="pg-topbar__chat">
				<a href="<?php echo esc_url( $chat_url ? $chat_url : '#' ); ?>">
					<?php packgens_icon( 'message-circle' ); ?>
					<span><?php echo esc_html( $chat_label ); ?></span>
				</a>
			</p>
		<?php endif; ?>

	</div>
</div>
