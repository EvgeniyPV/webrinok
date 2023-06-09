<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package webrhinoc
 */

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
<header class="header">
	<div class="container">
		<div class="header__burger">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3.11111 18.6666H20.8889C21.5 18.6666 22 18.1666 22 17.5555C22 16.9444 21.5 16.4444 20.8889 16.4444H3.11111C2.5 16.4444 2 16.9444 2 17.5555C2 18.1666 2.5 18.6666 3.11111 18.6666ZM3.11111 13.1111H20.8889C21.5 13.1111 22 12.6111 22 12C22 11.3889 21.5 10.8889 20.8889 10.8889H3.11111C2.5 10.8889 2 11.3889 2 12C2 12.6111 2.5 13.1111 3.11111 13.1111ZM2 6.44442C2 7.05554 2.5 7.55554 3.11111 7.55554H20.8889C21.5 7.55554 22 7.05554 22 6.44442C22 5.83331 21.5 5.33331 20.8889 5.33331H3.11111C2.5 5.33331 2 5.83331 2 6.44442Z" fill="#3E2780"/>
			</svg>
			<svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="none">
				<g id="Menu / Close_MD">
					<path id="Vector" d="M18 18L12 12M12 12L6 6M12 12L18 6M12 12L6 18" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</g>
			</svg>
		</div>
		<a href="" class="header__logo">
			<img src="<?php the_field('header_logo', 'options'); ?>" alt="">
		</a>
			<?php
			$args = array(
				'theme_location' => 'menu-1',
				'container' => false,
				'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'menu_class' => 'top-menu',
				'link_class' => '<?php echo $row[`class`] ?>__link',
			);
			wp_nav_menu($args);
			?>
		<?php while (have_rows('header_bag', 'options')): the_row() ?>
			<a href="<?php the_sub_field('link'); ?>" class="header__bag">
				<?php the_sub_field('icon'); ?>
				<?php the_sub_field('icon_hover'); ?>
			</a>
		<?php endwhile; ?>
	</div>
</header>
<main>
