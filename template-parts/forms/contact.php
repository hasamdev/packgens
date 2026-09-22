<?php
/**
 * Contact / support form.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$form_title = $args['title'] ?? packgens_label( 'forms', 'contact_title', __( 'Contact Our Support Team', 'packgens' ) );
$categories = packgens_quote_categories();
$form_id    = packgens_unique_id( 'pg-contact' );
?>
<form class="pg-form pg-form--contact"
	id="<?php echo esc_attr( $form_id ); ?>"
	method="post"
	enctype="multipart/form-data"
	action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
	data-pg-form>

	<input type="hidden" name="action" value="packgens_submit_form">
	<input type="hidden" name="pg_form" value="contact">
	<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'packgens_public' ) ); ?>">
	<input type="hidden" name="pg_source" value="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>">

	<p class="pg-hp" aria-hidden="true">
		<label for="<?php echo esc_attr( $form_id ); ?>-website"><?php esc_html_e( 'Leave this field empty', 'packgens' ); ?></label>
		<input type="text" id="<?php echo esc_attr( $form_id ); ?>-website" name="pg_website" tabindex="-1" autocomplete="off">
	</p>

	<?php if ( $form_title ) : ?>
		<h2 class="pg-form__title"><?php echo esc_html( $form_title ); ?></h2>
	<?php endif; ?>

	<div class="pg-form__body">
		<div class="pg-form__grid">

			<div class="pg-form__row pg-form__row--2">
				<div class="pg-field">
					<label class="pg-field__label" for="<?php echo esc_attr( $form_id ); ?>-name">
						<?php packgens_the_label( 'forms', 'name_label', __( 'Full Name', 'packgens' ) ); ?><span class="pg-required" aria-hidden="true">*</span>
					</label>
					<input class="pg-input" type="text" id="<?php echo esc_attr( $form_id ); ?>-name" name="name"
						placeholder="<?php echo esc_attr( packgens_label( 'forms', 'name_placeholder', __( 'Enter Full Name', 'packgens' ) ) ); ?>" autocomplete="name" required>
				</div>

				<div class="pg-field">
					<label class="pg-field__label" for="<?php echo esc_attr( $form_id ); ?>-email">
						<?php packgens_the_label( 'forms', 'email_label', __( 'Email', 'packgens' ) ); ?><span class="pg-required" aria-hidden="true">*</span>
					</label>
					<input class="pg-input" type="email" id="<?php echo esc_attr( $form_id ); ?>-email" name="email"
						placeholder="<?php echo esc_attr( packgens_label( 'forms', 'email_placeholder', __( 'Enter your Email', 'packgens' ) ) ); ?>" autocomplete="email" required>
				</div>
			</div>

			<div class="pg-form__row pg-form__row--2">
				<div class="pg-field">
					<label class="pg-field__label" for="<?php echo esc_attr( $form_id ); ?>-phone"><?php packgens_the_label( 'forms', 'phone_label', __( 'Phone Number', 'packgens' ) ); ?></label>
					<input class="pg-input" type="tel" id="<?php echo esc_attr( $form_id ); ?>-phone" name="phone"
						placeholder="<?php echo esc_attr( packgens_label( 'forms', 'phone_placeholder', __( 'Enter your Phone', 'packgens' ) ) ); ?>" autocomplete="tel">
				</div>

				<div class="pg-field">
					<label class="pg-field__label" for="<?php echo esc_attr( $form_id ); ?>-category"><?php packgens_the_label( 'forms', 'category_label', __( 'Category', 'packgens' ) ); ?></label>
					<select class="pg-select" id="<?php echo esc_attr( $form_id ); ?>-category" name="category">
						<option value=""><?php packgens_the_label( 'forms', 'category_empty', __( 'Select Category', 'packgens' ) ); ?></option>
						<?php foreach ( $categories as $category ) : ?>
							<option value="<?php echo esc_attr( $category ); ?>"><?php echo esc_html( $category ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="pg-form__row pg-form__row--2">
				<div class="pg-field">
					<label class="pg-field__label" for="<?php echo esc_attr( $form_id ); ?>-quantity"><?php packgens_the_label( 'forms', 'quantity_label', __( 'Quantity', 'packgens' ) ); ?></label>
					<input class="pg-input" type="text" inputmode="numeric" id="<?php echo esc_attr( $form_id ); ?>-quantity" name="quantity"
						placeholder="<?php echo esc_attr( packgens_label( 'forms', 'quantity_placeholder', __( 'Quantity', 'packgens' ) ) ); ?>">
				</div>

				<div class="pg-field">
					<label class="pg-field__label" for="<?php echo esc_attr( $form_id ); ?>-zip"><?php packgens_the_label( 'forms', 'zip_label', __( 'Zip Code', 'packgens' ) ); ?></label>
					<input class="pg-input" type="text" id="<?php echo esc_attr( $form_id ); ?>-zip" name="zip"
						placeholder="<?php echo esc_attr( packgens_label( 'forms', 'zip_placeholder', __( 'Enter zip code', 'packgens' ) ) ); ?>" autocomplete="postal-code">
				</div>
			</div>

			<div class="pg-field">
				<label class="pg-field__label" for="<?php echo esc_attr( $form_id ); ?>-message">
					<?php packgens_the_label( 'forms', 'message_label', __( 'Project Details', 'packgens' ) ); ?><span class="pg-required" aria-hidden="true">*</span>
				</label>
				<textarea class="pg-textarea" id="<?php echo esc_attr( $form_id ); ?>-message" name="message" rows="5"
					placeholder="<?php echo esc_attr( packgens_label( 'forms', 'message_placeholder', __( 'Enter your project details...', 'packgens' ) ) ); ?>" required></textarea>
			</div>

		</div>

		<div class="pg-form__actions">
			<label class="pg-file">
				<?php packgens_icon( 'paperclip' ); ?>
				<span><?php packgens_the_label( 'forms', 'file_label', __( 'Attach or Browse Files', 'packgens' ) ); ?></span>
				<input type="file" name="artwork" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.ai,.eps,.psd,.zip"
				aria-label="<?php esc_attr_e( 'Attach artwork files', 'packgens' ); ?>" data-pg-file>
				<span class="pg-file__name" data-pg-file-name></span>
			</label>

			<button type="submit" class="pg-btn pg-btn--primary">
				<span class="pg-btn__label"><?php packgens_the_label( 'forms', 'contact_submit', __( 'Send Free Quote', 'packgens' ) ); ?></span>
				<?php packgens_icon( 'arrow-right', 'pg-btn__icon' ); ?>
			</button>
		</div>

		<p class="pg-form__message" role="status" aria-live="polite" data-pg-form-message></p>
	</div>

</form>
