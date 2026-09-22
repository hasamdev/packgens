<?php
/**
 * Template Name: Contact
 *
 * Hero, support form beside the contact channel cards, then the page's own
 * sections.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_the_ID();

packgens_render_section( 'hero', $page_id, 'page' );

if ( ! packgens_field( 'hero_section', $page_id ) ) {
	get_template_part( 'template-parts/components/page-header' );
}

$data    = packgens_field( 'contact_section', $page_id, array() );
$trust   = (array) ( $data['trust'] ?? array() );

$phone   = packgens_get_option( 'phone' );
$email   = packgens_get_option( 'email' );
$address = packgens_get_option( 'address' );
$hours   = packgens_get_option( 'opening_hours' );
$chat    = packgens_get_option( 'topbar_chat_url' );
$socials = packgens_social_links();
?>

<section class="pg-section pg-section--contact">
	<div class="pg-container">

		<div class="pg-section-head pg-section-head--center">
			<div class="pg-section-head__copy">
				<h2 class="pg-section-head__title"><?php echo esc_html( $data['title'] ?? __( 'Still Have Questions?', 'packgens' ) ); ?></h2>
				<?php if ( ! empty( $data['description'] ) ) : ?>
					<p class="pg-section-head__desc"><?php echo esc_html( $data['description'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="pg-contact">

			<div class="pg-contact__form">
				<?php get_template_part( 'template-parts/forms/contact' ); ?>
			</div>

			<div class="pg-contact__channels">
				<?php if ( $phone ) : ?>
					<div class="pg-channel">
						<span class="pg-channel__icon"><?php packgens_icon( 'contact-phone' ); ?></span>
						<h3 class="pg-channel__title"><?php packgens_the_label( 'contact', 'call_title', __( 'Call Us', 'packgens' ) ); ?></h3>
						<p class="pg-channel__value"><a href="<?php echo esc_url( packgens_tel_href( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
						<p class="pg-channel__note"><?php echo esc_html( $data['call_note'] ?? __( 'Response within 24-48 hours', 'packgens' ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $email ) : ?>
					<div class="pg-channel">
						<span class="pg-channel__icon"><?php packgens_icon( 'contact-mail' ); ?></span>
						<h3 class="pg-channel__title"><?php packgens_the_label( 'contact', 'email_title', __( 'Send Email', 'packgens' ) ); ?></h3>
						<p class="pg-channel__value"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
						<p class="pg-channel__note"><?php echo esc_html( $data['email_note'] ?? __( 'Send any query to our dedicated team', 'packgens' ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $chat ) : ?>
					<div class="pg-channel">
						<span class="pg-channel__icon"><?php packgens_icon( 'contact-chat' ); ?></span>
						<h3 class="pg-channel__title"><?php packgens_the_label( 'contact', 'chat_title', __( 'Message Us', 'packgens' ) ); ?></h3>
						<p class="pg-channel__note"><?php echo esc_html( $data['chat_note'] ?? __( 'Start a chat with a packaging expert.', 'packgens' ) ); ?></p>
						<a class="pg-btn pg-btn--primary pg-btn--sm" href="<?php echo esc_url( $chat ); ?>">
							<?php packgens_icon( 'message-circle', 'pg-btn__icon' ); ?>
							<?php echo esc_html( $data['chat_label'] ?? __( 'Live Chat', 'packgens' ) ); ?>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( $address ) : ?>
					<div class="pg-channel">
						<span class="pg-channel__icon"><?php packgens_icon( 'contact-pin' ); ?></span>
						<h3 class="pg-channel__title"><?php packgens_the_label( 'contact', 'address_title', __( 'Office Address', 'packgens' ) ); ?></h3>
						<p class="pg-channel__value"><?php echo nl2br( esc_html( $address ) ); ?></p>
						<?php if ( $hours ) : ?>
							<p class="pg-channel__note"><?php echo nl2br( esc_html( $hours ) ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $socials ) : ?>
					<div class="pg-channel pg-channel--social">
						<h3 class="pg-channel__title"><?php echo esc_html( $data['follow_label'] ?? __( 'Follow Us', 'packgens' ) ); ?></h3>
						<ul class="pg-social">
							<?php foreach ( $socials as $icon => $url ) : ?>
								<li>
									<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
										<?php packgens_icon( $icon ); ?>
										<span class="pg-sr-only"><?php echo esc_html( ucfirst( $icon ) ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>

		</div>

		<?php
		$points = array_filter(
			(array) ( $trust['points'] ?? array() ),
			static function ( $point ) {
				return ! empty( $point['text'] );
			}
		);

		if ( ! empty( $trust['title'] ) && ( ! isset( $trust['enable'] ) || $trust['enable'] ) ) :
			?>
			<div class="pg-trust">
				<div class="pg-trust__intro">
					<h2 class="pg-trust__title"><?php echo esc_html( $trust['title'] ); ?></h2>

					<?php if ( ! empty( $trust['rating_value'] ) ) : ?>
						<p class="pg-trust__rating">
							<span class="pg-trust__score">
								<?php packgens_icon( 'star-review' ); ?>
								<?php echo esc_html( $trust['rating_value'] ); ?>
							</span>
							<?php if ( ! empty( $trust['rating_count'] ) ) : ?>
								<span class="pg-trust__count"><?php echo esc_html( $trust['rating_count'] ); ?></span>
							<?php endif; ?>
						</p>
					<?php endif; ?>
				</div>

				<div class="pg-trust__body">
					<?php if ( ! empty( $trust['image'] ) ) : ?>
						<figure class="pg-trust__media">
							<?php echo wp_kses_post( packgens_image( $trust['image'], 'packgens-card', array( 'alt' => '' ) ) ); ?>
						</figure>
					<?php endif; ?>

					<?php if ( $points ) : ?>
						<ul class="pg-trust__points">
							<?php foreach ( $points as $point ) : ?>
								<li>
									<?php packgens_icon( 'check-filled' ); ?>
									<span><?php echo esc_html( $point['text'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
if ( trim( (string) get_post_field( 'post_content', $page_id ) ) ) :
	?>
	<section class="pg-section pg-section--page-content">
		<div class="pg-container pg-container--narrow">
			<div class="pg-prose">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</div>
	</section>
	<?php
endif;

packgens_render_sections( 'page', $page_id, array(), array( 'hero' ) );

get_footer();
