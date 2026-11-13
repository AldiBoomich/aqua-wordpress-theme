<?php
/**
 * Admin UX: "How to edit this website" dashboard widget and icon previews in ACF fields.
 *
 * @package aqua
 */

/**
 * Registers the dashboard help widget and moves it to the top.
 */
function aqua_dashboard_widget() {
	wp_add_dashboard_widget( 'aqua_help', 'How to edit this website', 'aqua_dashboard_widget_render' );

	global $wp_meta_boxes;
	$normal = $wp_meta_boxes['dashboard']['normal']['core'] ?? array();
	if ( isset( $normal['aqua_help'] ) ) {
		$wp_meta_boxes['dashboard']['normal']['core'] = array( 'aqua_help' => $normal['aqua_help'] ) + $normal; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
add_action( 'wp_dashboard_setup', 'aqua_dashboard_widget' );

/**
 * Dashboard help widget content.
 */
function aqua_dashboard_widget_render() {
	$home_id = aqua_front_page_id();
	$items   = array(
		array(
			'icon'  => 'dashicons-slides',
			'title' => 'Homepage slider & blocks',
			'text'  => 'Slider, features, about, services, stats, price list and blog headings.',
			'url'   => $home_id ? get_edit_post_link( $home_id ) : admin_url( 'edit.php?post_type=page' ),
		),
		array(
			'icon'  => 'dashicons-location',
			'title' => 'Contacts, map & social links',
			'text'  => 'Address, phone, e-mail, map, social networks, newsletter form, 404 page.',
			'url'   => admin_url( 'admin.php?page=theme-general-settings' ),
		),
		array(
			'icon'  => 'dashicons-admin-appearance',
			'title' => 'Header & footer',
			'text'  => 'Logo, phone, header button, footer texts, opening hours, copyright.',
			'url'   => admin_url( 'admin.php?page=theme-header-settings' ),
		),
		array(
			'icon'  => 'dashicons-menu',
			'title' => 'Menus',
			'text'  => 'Header and footer navigation links.',
			'url'   => admin_url( 'nav-menus.php' ),
		),
		array(
			'icon'  => 'dashicons-email',
			'title' => 'Forms',
			'text'  => 'Contact and newsletter forms, e-mail notifications.',
			'url'   => admin_url( 'admin.php?page=wpcf7' ),
		),
		array(
			'icon'  => 'dashicons-admin-post',
			'title' => 'Blog posts',
			'text'  => 'Write news and tips — the latest posts appear on the homepage.',
			'url'   => admin_url( 'edit.php' ),
		),
	);
	?>
	<ul class="aqua-help">
		<?php foreach ( $items as $item ) : ?>
			<li class="aqua-help__item">
				<span class="aqua-help__icon dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
				<span>
					<a class="aqua-help__title" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
					<span class="aqua-help__text"><?php echo esc_html( $item['text'] ); ?></span>
				</span>
			</li>
		<?php endforeach; ?>
	</ul>
	<style>
		.aqua-help { margin: 0; }
		.aqua-help__item { display: flex; gap: 12px; margin: 0; padding: 12px 0; border-bottom: 1px solid #f0f0f1; }
		.aqua-help__item:last-child { border-bottom: 0; }
		.aqua-help__icon { flex: 0 0 36px; width: 36px; height: 36px; border-radius: 8px; background: #e6f9fe; color: #19409a; font-size: 20px; line-height: 36px; text-align: center; }
		.aqua-help__title { display: block; font-weight: 600; text-decoration: none; }
		.aqua-help__text { color: #646970; }
	</style>
	<?php
}

/**
 * Font Awesome + icon preview script on screens with ACF fields.
 */
function aqua_acf_admin_assets() {
	$uri = get_template_directory_uri() . '/assets';
	wp_enqueue_style( 'aqua-fontawesome', 'https://use.fontawesome.com/releases/v5.15.4/css/all.css', array(), '5.15.4' );
	wp_enqueue_style( 'aqua-acf-admin', $uri . '/admin/acf-admin.css', array(), _S_VERSION );
	wp_enqueue_script( 'aqua-acf-icons', $uri . '/admin/acf-icons.js', array( 'acf-input' ), _S_VERSION, true );
}
add_action( 'acf/input/admin_enqueue_scripts', 'aqua_acf_admin_assets' );
