<?php
/**
 * Footer newsletter sign-up.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$form_id = packgens_unique_id( 'pg-news' );
?>
<form class="pg-form pg-form--newsletter"
	method="post"
	action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
	data-pg-form>

	<input type="hidden" name="action" value="packgens_submit_form">
	<input type="hidden" name="pg_form" value="newsletter">
	<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'packgens_public' ) ); ?>">

	<p class="pg-hp" aria-hidden="true">
		<label for="<?php echo esc_attr( $form_id ); ?>-website"><?php esc_html_e( 'Leave this field empty', 'packgens' ); ?></label>
		<input type="text" id="<?php echo esc_attr( $form_id ); ?>-website" name="pg_website" tabindex="-1" autocomplete="off">
	</p>

	<div class="pg-newsletter__field">
		<label class="pg-sr-only" for="<?php echo esc_attr( $form_id ); ?>-email"><?php esc_html_e( 'Email address', 'packgens' ); ?></label>
		<input type="email"
			id="<?php echo esc_attr( $form_id ); ?>-email"
			name="email"
			placeholder="<?php echo esc_attr( packgens_label( 'forms', 'newsletter_placeholder', __( 'type: your@email.com', 'packgens' ) ) ); ?>"
			autocomplete="email"
			required>
		<button type="submit" class="pg-newsletter__submit">
			<?php packgens_icon( 'arrow-right' ); ?>
			<span class="pg-sr-only"><?php esc_html_e( 'Subscribe', 'packgens' ); ?></span>
		</button>
	</div>

	<p class="pg-form__message" role="status" aria-live="polite" data-pg-form-message></p>
</form>
