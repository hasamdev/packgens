<?php
/**
 * Site footer.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;
?>
	</main><!-- #pg-content -->

	<?php get_template_part( 'template-parts/footer/usp-strip' ); ?>

	<footer class="pg-footer" role="contentinfo">
		<div class="pg-container">

			<div class="pg-footer__grid">
				<?php
				get_template_part( 'template-parts/footer/brand-column' );
				get_template_part( 'template-parts/footer/link-columns' );
				get_template_part( 'template-parts/footer/social' );
				get_template_part( 'template-parts/footer/payments' );
				?>
			</div>

			<?php get_template_part( 'template-parts/footer/bottom-bar' ); ?>

		</div>
	</footer>

</div><!-- .pg-site -->

<?php wp_footer(); ?>
</body>
</html>
