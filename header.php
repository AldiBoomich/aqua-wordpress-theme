<?php
/**
 * Site header: <head>, preloader and navigation bar,
 * followed by the hero slider (homepage) or the page banner (inner pages).
 *
 * Fields: Theme Settings → Header.
 *
 * @package aqua
 */

$aqua_logo        = aqua_field( 'logo', get_theme_mod( 'custom_logo' ) );
$aqua_logo_text   = aqua_field( 'logo_text', get_bloginfo( 'name' ) );
$aqua_phone_label = aqua_field( 'header_delivery_text', 'Free Delivery Hotline' );
$aqua_phone_text  = aqua_field( 'header_phone_text', '+1 (800) 555-0199' );
$aqua_phone       = aqua_field( 'header_phone', '+18005550199' );
$aqua_cta         = aqua_link(
	aqua_field( 'header_button' ),
	array(
		'title' => 'Order Water',
		'url'   => aqua_page_url( 'products' ),
	)
);
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<a class="visually-hidden-focusable" href="#main">Skip to content</a>

	<div class="preloader" id="preloader" role="status" aria-label="Loading">
		<span class="preloader__spinner"></span>
	</div>

	<?php // .navbar: Bootstrap dropdowns inside the header open without Popper (static sub menu on mobile). ?>
	<header class="site-header navbar" id="site-header">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-header__logo" rel="home">
			<?php if ( $aqua_logo ) : ?>
				<?php echo aqua_image_tag( $aqua_logo, '', 'medium', array( 'class' => 'site-header__logo-image', 'alt' => $aqua_logo_text, 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<?php echo aqua_icon( 'fas fa-tint', 'site-header__logo-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="site-header__logo-text"><?php echo esc_html( $aqua_logo_text ); ?></span>
			<?php endif; ?>
		</a>

		<?php // Mobile menu button (theme.js): three lines that turn into a cross. ?>
		<button class="site-header__toggler" type="button" aria-controls="site-menu" aria-expanded="false" aria-label="Open menu" data-label-open="Open menu" data-label-close="Close menu">
			<span class="site-header__toggler-line" aria-hidden="true"></span>
			<span class="site-header__toggler-line" aria-hidden="true"></span>
			<span class="site-header__toggler-line" aria-hidden="true"></span>
		</button>

		<div class="site-header__backdrop" aria-hidden="true"></div>

		<?php // Mobile: slide-in panel. Desktop (≥992px): its children join the header grid. ?>
		<div class="site-header__menu" id="site-menu">
			<nav class="site-header__nav" aria-label="Main">
				<?php
				// Appearance → Menus → "Header Menu".
				wp_nav_menu(
					array(
						'theme_location' => 'header',
						'container'      => false,
						'items_wrap'     => '<ul class="main-nav">%3$s</ul>',
						'depth'          => 2,
						'walker'         => new Aqua_Nav_Walker(),
						'fallback_cb'    => 'aqua_menu_fallback',
					)
				);
				?>
			</nav>

			<div class="site-header__actions">
				<?php if ( $aqua_phone_text ) : ?>
					<div class="site-header__phone">
						<span class="site-header__phone-icon" aria-hidden="true"><i class="fas fa-phone-alt"></i></span>
						<span class="site-header__phone-text">
							<span class="site-header__phone-label"><?php echo esc_html( $aqua_phone_label ); ?></span>
							<a class="site-header__phone-number" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>"><?php echo esc_html( $aqua_phone_text ); ?></a>
						</span>
					</div>
				<?php endif; ?>

				<?php echo aqua_link_tag( $aqua_cta, 'site-header__cta button button--primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php get_template_part( 'template-parts/social-links', null, array( 'block' => 'site-header' ) ); ?>
			</div>
		</div>
	</header>

	<?php
	if ( is_front_page() ) {
		get_template_part( 'template-parts/sections/hero' );
	} else {
		aqua_breadcrumb();
	}
	?>

	<main id="main" class="site-main">
