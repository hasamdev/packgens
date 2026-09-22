<?php
/**
 * Document head and site header.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="pg-skip-link" href="#pg-content"><?php esc_html_e( 'Skip to content', 'packgens' ); ?></a>

<div class="pg-site">

	<header id="pg-header" class="pg-header" role="banner">
		<?php get_template_part( 'template-parts/header/topbar' ); ?>

		<div class="pg-container">
			<div class="pg-header__card">
				<?php
				get_template_part( 'template-parts/header/utility-row' );
				get_template_part( 'template-parts/header/nav-row' );
				?>
			</div>
		</div>

		<?php get_template_part( 'template-parts/header/strip' ); ?>
	</header>

	<?php get_template_part( 'template-parts/header/mobile-drawer' ); ?>

	<main id="pg-content" class="pg-main" role="main">
