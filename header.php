<?php
/**
 * Site header: <head>, the fixed header bar and the full-screen mobile menu,
 * followed by the top section of the page (homepage hero / page banner while those are rebuilt).
 *
 * Fields: Theme Settings → Header (logo, phone, button), Theme Settings → Labels.
 *
 * @package aqua
 */

$aqua_phone_label = aqua_field( 'header_delivery_text', 'Free delivery hotline' );
$aqua_phone_text  = aqua_field( 'header_phone_text', '+1 (800) 555-0199' );
$aqua_phone       = aqua_field( 'header_phone', '+18005550199' );
$aqua_email       = aqua_field( 'contact_email', 'hello@example.com' );
$aqua_cta         = aqua_link(
	aqua_field( 'header_button' ),
	array(
		'title' => 'Order water',
		'url'   => aqua_page_url( 'products' ),
	)
);
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<a class="skip-link" href="#main"><?php echo esc_html( aqua_label( 'skip', 'Skip to content' ) ); ?></a>

	<header class="site-header site-header--<?php echo esc_attr( aqua_header_tone() ); ?>" id="site-header" data-header>
		<div class="site-header__inner wrap">
			<?php get_template_part( 'template-parts/brand', null, array( 'class' => 'site-header__brand' ) ); ?>

			<nav class="site-header__nav" aria-label="<?php echo esc_attr( aqua_label( 'nav_main', 'Main' ) ); ?>">
				<?php
				// Appearance → Menus → "Header Menu".
				wp_nav_menu(
					array(
						'theme_location' => 'header',
						'container'      => false,
						'items_wrap'     => '<ul class="main-nav">%3$s</ul>',
						'depth'          => 2,
						'walker'         => new Aqua_Nav_Walker( 'main-nav' ),
						'fallback_cb'    => 'aqua_menu_fallback',
						'aqua_block'     => 'main-nav',
					)
				);
				?>
			</nav>

			<div class="site-header__actions">
				<?php if ( $aqua_phone_text ) : ?>
					<a class="site-header__phone" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>">
						<?php echo aqua_ui_icon( 'phone', 'site-header__phone-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
						<span><span class="screen-reader-text"><?php echo esc_html( $aqua_phone_label ); ?>: </span><?php echo esc_html( $aqua_phone_text ); ?></span>
					</a>
				<?php endif; ?>

				<?php echo aqua_link_tag( $aqua_cta, 'button button--primary button--small site-header__cta' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_link_tag(). ?>

				<button class="site-header__toggle" type="button" aria-controls="menu-overlay" aria-expanded="false"
					aria-label="<?php echo esc_attr( aqua_label( 'menu_open', 'Open menu' ) ); ?>"
					data-label-open="<?php echo esc_attr( aqua_label( 'menu_open', 'Open menu' ) ); ?>"
					data-label-close="<?php echo esc_attr( aqua_label( 'menu_close', 'Close menu' ) ); ?>"
					data-menu-toggle>
					<span class="site-header__toggle-line" aria-hidden="true"></span>
					<span class="site-header__toggle-line" aria-hidden="true"></span>
				</button>
			</div>
		</div>
	</header>

	<?php // Full-screen menu (phones, tablets). A sibling of the header on purpose — see site-header.css. ?>
	<div class="menu-overlay" id="menu-overlay" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?php echo esc_attr( aqua_label( 'menu', 'Menu' ) ); ?>">
		<div class="menu-overlay__inner wrap">
			<nav class="menu-overlay__nav" aria-label="<?php echo esc_attr( aqua_label( 'nav_main', 'Main' ) ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'header',
						'container'      => false,
						'items_wrap'     => '<ul class="overlay-nav">%3$s</ul>',
						'depth'          => 2,
						'walker'         => new Aqua_Nav_Walker( 'overlay-nav' ),
						'fallback_cb'    => 'aqua_menu_fallback',
						'aqua_block'     => 'overlay-nav',
					)
				);
				?>
			</nav>

			<div class="menu-overlay__footer">
				<?php echo aqua_link_tag( $aqua_cta, 'button button--primary button--large button--block' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_link_tag(). ?>

				<div class="menu-overlay__contacts">
					<?php if ( $aqua_phone_text ) : ?>
						<p>
							<span class="menu-overlay__label"><?php echo esc_html( $aqua_phone_label ); ?></span>
							<a class="menu-overlay__value" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>"><?php echo esc_html( $aqua_phone_text ); ?></a>
						</p>
					<?php endif; ?>
					<?php if ( $aqua_email ) : ?>
						<p>
							<span class="menu-overlay__label"><?php echo esc_html( aqua_label( 'email', 'Email' ) ); ?></span>
							<a class="menu-overlay__value" href="mailto:<?php echo esc_attr( antispambot( $aqua_email ) ); ?>"><?php echo esc_html( antispambot( $aqua_email ) ); ?></a>
						</p>
					<?php endif; ?>
				</div>

				<?php get_template_part( 'template-parts/social-links', null, array( 'block' => 'menu-overlay' ) ); ?>
			</div>
		</div>
	</div>

	<?php
	if ( is_front_page() ) {
		get_template_part( 'template-parts/sections/hero' );
	} elseif ( ! aqua_has_page_head() ) {
		aqua_breadcrumb();
	}
	?>

	<main id="main" class="site-main">
