<?php
/**
 * Footer template file
 *
 * @package estore/theme
 * @since 0.0.1
 */

?>
	<footer class="website-footer">
		<div class="menus">
			<?php for ( $i = 1; $i < 4; ++$i ) : ?>
				<div class="footer-menu">
					<?php if ( has_nav_menu( 'footer-menu-' . $i ) ) : ?>
						<?php
						wp_nav_menu(
							[
								'menu' => 'footer-menu-' . $i,
							]
						);
						?>
					<?php endif; ?>
				</div>
			<?php endfor; ?>
		</div>
		<div class="credentials">
			<span class="copyright">
				<?php echo esc_html( get_option( 'copyright-text' ) ); ?>
			</span>
			<span class="developer">
				<?php esc_html_e( 'Розробка:', 'estore-theme' ); ?>
				<a class="dev-link" href="mailto:dm.ilch.mail@gmail.com">
					Dmytro Ilchenko
				</a>
			</span>
		</div>
	</footer>
</body>
</html>
<?php
wp_footer();


