<?php
/**
 * Single post sidebar: contents, a trust card and the share row.
 *
 * The blog index has its own sidebar in post/sidebar.php; this one belongs to
 * the article page.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$post_id = absint( $args['post_id'] ?? get_the_ID() );
$toc     = (array) ( $args['toc'] ?? array() );
// packgens_global() reads one sub-key; the whole group is wanted here.
$card    = (array) packgens_field( 'post_aside', 'option', array() );
$points  = array_filter(
	(array) ( $card['points'] ?? array() ),
	static function ( $point ) {
		return ! empty( $point['text'] );
	}
);
$button  = packgens_link( $card['button'] ?? null );
$share   = packgens_share_links( $post_id );
$share['link'] = get_permalink( $post_id );
?>
<aside class="pg-post-aside">

	<?php if ( $toc ) : ?>
		<details class="pg-post-toc">
			<summary class="pg-post-toc__summary">
				<?php packgens_the_label( 'blog', 'toc_title', __( 'Table of Contents', 'packgens' ) ); ?>
				<?php packgens_icon( 'chevron-down', 'pg-post-toc__caret' ); ?>
			</summary>

			<ol class="pg-post-toc__list">
				<?php foreach ( $toc as $item ) : ?>
					<li><a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</details>
	<?php endif; ?>

	<?php if ( ! empty( $card['title'] ) ) : ?>
		<section class="pg-post-promo">

			<div class="pg-post-promo__head">
				<h2 class="pg-post-promo__title"><?php echo esc_html( $card['title'] ); ?></h2>

				<?php if ( ! empty( $card['rating'] ) ) : ?>
					<span class="pg-post-promo__rating">
						<span class="pg-post-promo__score">
							<?php packgens_icon( 'star-gold' ); ?>
							<?php echo esc_html( $card['rating'] ); ?>
						</span>
						<?php if ( ! empty( $card['rating_note'] ) ) : ?>
							<span class="pg-post-promo__reviews"><?php echo esc_html( $card['rating_note'] ); ?></span>
						<?php endif; ?>
					</span>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $card['image'] ) ) : ?>
				<figure class="pg-post-promo__media">
					<?php packgens_image( $card['image'], 'packgens-card', array( 'loading' => 'lazy' ), '' ); ?>
				</figure>
			<?php endif; ?>

			<?php if ( $points || $button ) : ?>
				<div class="pg-post-promo__foot">
					<?php if ( $points ) : ?>
						<ul class="pg-checklist">
							<?php foreach ( $points as $point ) : ?>
								<li>
									<?php packgens_icon( 'check-filled' ); ?>
									<span><?php echo esc_html( $point['text'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( $button ) : ?>
						<a class="pg-btn pg-btn--primary pg-btn--block" href="<?php echo esc_url( $button['url'] ); ?>">
							<?php packgens_icon( 'calculator', 'pg-btn__icon' ); ?>
							<?php echo esc_html( $button['title'] ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</section>
	<?php endif; ?>

	<?php if ( $share ) : ?>
		<div class="pg-post-share">
			<span class="pg-post-share__label"><?php packgens_the_label( 'blog', 'share_title', __( 'Share This Article', 'packgens' ) ); ?></span>

			<ul class="pg-post-share__list">
				<?php
				foreach ( $share as $network => $target ) :
					// The last entry copies the address rather than opening a window.
					$is_copy = 'link' === $network;
					?>
					<li>
						<a class="pg-post-share__link pg-post-share__link--<?php echo esc_attr( $network ); ?>"
							href="<?php echo esc_url( $target ); ?>"
							<?php echo $is_copy ? ' data-pg-copy="' . esc_url( $target ) . '"' : ' target="_blank" rel="noopener"'; ?>>
							<?php packgens_icon( 'x' === $network ? 'close' : $network ); ?>
							<span class="pg-sr-only">
								<?php
								echo $is_copy
									? esc_html__( 'Copy link', 'packgens' )
									: esc_html( sprintf( /* translators: %s: network name. */ __( 'Share on %s', 'packgens' ), ucfirst( $network ) ) );
								?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

</aside>
