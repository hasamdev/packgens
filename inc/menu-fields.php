<?php
/**
 * Per-menu-item image field, used by the mega menu cards.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta key holding the attachment ID for a menu item.
 */
const PACKGENS_MENU_IMAGE_KEY = '_packgens_menu_image';

/**
 * Load the media modal on the menus screen.
 *
 * @param string $hook_suffix Current admin page.
 * @return void
 */
function packgens_menu_admin_assets( $hook_suffix ) {
	if ( 'nav-menus.php' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_media();
	packgens_register_script( 'packgens-menu-fields', 'admin/menu-fields.js', array( 'jquery' ) );
	wp_enqueue_script( 'packgens-menu-fields' );
	wp_localize_script(
		'packgens-menu-fields',
		'packgensMenuFields',
		array(
			'title'  => __( 'Select menu item image', 'packgens' ),
			'button' => __( 'Use this image', 'packgens' ),
			'remove' => __( 'Remove image', 'packgens' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'packgens_menu_admin_assets' );

/**
 * Print the image control inside each menu item panel.
 *
 * @param int      $item_id Menu item ID.
 * @param WP_Post  $item    Menu item.
 * @param int      $depth   Depth.
 * @param stdClass $args    Menu arguments.
 * @return void
 */
function packgens_menu_item_image_field( $item_id, $item, $depth, $args ) {
	$image_id = (int) get_post_meta( $item_id, PACKGENS_MENU_IMAGE_KEY, true );
	$preview  = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	?>
	<p class="field-packgens-image description description-wide packgens-menu-image" data-item-id="<?php echo esc_attr( $item_id ); ?>">
		<label for="packgens-menu-image-<?php echo esc_attr( $item_id ); ?>">
			<?php esc_html_e( 'Mega menu image', 'packgens' ); ?>
		</label>
		<span class="packgens-menu-image__preview">
			<?php if ( $preview ) : ?>
				<img src="<?php echo esc_url( $preview ); ?>" alt="" width="60" height="60">
			<?php endif; ?>
		</span>
		<input type="hidden"
			id="packgens-menu-image-<?php echo esc_attr( $item_id ); ?>"
			class="packgens-menu-image__input"
			name="packgens_menu_image[<?php echo esc_attr( $item_id ); ?>]"
			value="<?php echo esc_attr( $image_id ); ?>">
		<button type="button" class="button packgens-menu-image__select"><?php esc_html_e( 'Choose image', 'packgens' ); ?></button>
		<button type="button" class="button-link packgens-menu-image__clear"<?php echo $image_id ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove', 'packgens' ); ?></button>
	</p>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'packgens_menu_item_image_field', 10, 4 );

/**
 * Persist the image field.
 *
 * @param int $menu_id         Menu ID.
 * @param int $menu_item_db_id Menu item ID.
 * @return void
 */
function packgens_save_menu_item_image( $menu_id, $menu_item_db_id ) {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	// WordPress verifies the update-nav_menu nonce before this hook fires;
	// check it again so the handler is safe if called from anywhere else.
	if ( ! isset( $_POST['update-nav-menu-nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['update-nav-menu-nonce'] ) ), 'update-nav_menu' ) ) {
		return;
	}

	if ( ! isset( $_POST['packgens_menu_image'][ $menu_item_db_id ] ) ) {
		return;
	}

	$image_id = absint( wp_unslash( $_POST['packgens_menu_image'][ $menu_item_db_id ] ) );

	if ( $image_id && 'attachment' === get_post_type( $image_id ) ) {
		update_post_meta( $menu_item_db_id, PACKGENS_MENU_IMAGE_KEY, $image_id );
	} else {
		delete_post_meta( $menu_item_db_id, PACKGENS_MENU_IMAGE_KEY );
	}
}
add_action( 'wp_update_nav_menu_item', 'packgens_save_menu_item_image', 10, 2 );
