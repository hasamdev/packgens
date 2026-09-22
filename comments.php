<?php
/**
 * Comments template.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="pg-comments">

	<?php if ( have_comments() ) : ?>
		<h2 class="pg-comments__title">
			<?php
			$count = get_comments_number();

			printf(
				/* translators: %s: comment count. */
				esc_html( _n( '%s comment', '%s comments', $count, 'packgens' ) ),
				esc_html( number_format_i18n( $count ) )
			);
			?>
		</h2>

		<ol class="pg-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => esc_html__( 'Older comments', 'packgens' ),
				'next_text' => esc_html__( 'Newer comments', 'packgens' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="pg-comments__closed"><?php esc_html_e( 'Comments are closed.', 'packgens' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_submit'  => 'pg-btn pg-btn--primary',
			'title_reply'   => esc_html__( 'Leave a comment', 'packgens' ),
			'comment_field' => sprintf(
				'<p class="comment-form-comment pg-field"><label class="pg-field__label" for="comment">%s</label><textarea id="comment" class="pg-textarea" name="comment" rows="5" required></textarea></p>',
				esc_html__( 'Comment', 'packgens' )
			),
		)
	);
	?>

</div>
