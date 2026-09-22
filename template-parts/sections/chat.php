<?php
/**
 * Chat bar: a slim prompt inviting the visitor into live chat.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data = $args['data'] ?? array();

if ( empty( $data['title'] ) ) {
	return;
}
?>
<section class="<?php echo esc_attr( packgens_section_class( 'chat', '', 'pg-section--tight' ) ); ?>">
	<div class="pg-container">
		<div class="pg-chatbar">

			<div class="pg-chatbar__copy">
				<?php if ( ! empty( $data['icon'] ) ) : ?>
					<span class="pg-chatbar__icon"><?php packgens_icon( $data['icon'] ); ?></span>
				<?php endif; ?>

				<span>
					<span class="pg-chatbar__title"><?php echo esc_html( $data['title'] ); ?></span>

					<?php if ( ! empty( $data['description'] ) ) : ?>
						<span class="pg-chatbar__desc"><?php echo esc_html( $data['description'] ); ?></span>
					<?php endif; ?>
				</span>
			</div>

			<?php if ( ! empty( $data['button'] ) ) : ?>
				<?php packgens_button( $data['button'], 'outline-light', __( 'Chat with us', 'packgens' ), 'message-circle' ); ?>
			<?php endif; ?>

		</div>
	</div>
</section>
