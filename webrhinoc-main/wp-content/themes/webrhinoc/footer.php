<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package webrhinoc
 */

?>

</main>
<footer class="footer">
	<div class="container">
		<a href="" class="footer__logo">
			<img src="<?php the_field('footer_logo', 'options'); ?>" alt="">
		</a>
		<ul class="footer__links footer__links-1">
			<?php while (have_rows('footer_links_1', 'options')): the_row() ?>
				<li>
					<a href="<?php the_sub_field('link'); ?>">
						<?php the_sub_field('text'); ?>
					</a>
				</li>
			<?php endwhile; ?>
		</ul>
		<ul class="footer__links footer__links-2">
			<?php while (have_rows('footer_links_2', 'options')): the_row() ?>
				<li>
					<a href="<?php the_sub_field('link'); ?>">
						<?php the_sub_field('text'); ?>
					</a>
				</li>
			<?php endwhile; ?>
		</ul>
		<div class="footer__right">
			<ul class="footer__socials">
				<?php while (have_rows('footer_socials', 'options')): the_row() ?>
					<li>
						<a href="<?php the_sub_field('link'); ?>">
							<?php the_sub_field('icon_svg_code'); ?>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>
			<a href="<?php the_field('footer_email', 'options'); ?>" class="footer-email">
				<?php the_field('footer_email', 'options'); ?>
			</a>
			<div class="footer-copyright">
				<?php the_field('footer_copyright', 'options'); ?>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
