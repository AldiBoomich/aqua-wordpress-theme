<?php
/**
 * 404 page (block "error-page"). Texts: Theme Settings → 404 Page.
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package aqua
 */

get_header();
?>

<section class="error-page">
	<div class="container">
		<div class="error-page__inner wow fadeInUp" data-wow-delay="0.2s">
			<i class="error-page__icon fas fa-tint" aria-hidden="true"></i>
			<p class="error-page__code" aria-hidden="true">404</p>
			<h2 class="error-page__title"><?php echo esc_html( aqua_field( 'error_title', 'Page Not Found' ) ); ?></h2>
			<p class="error-page__text"><?php echo esc_html( aqua_field( 'error_text', 'We’re sorry, the page you have looked for does not exist on our website. Maybe go to our home page?' ) ); ?></p>
			<a class="button button--primary button--large" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go Back To Home</a>
		</div>
	</div>
</section>

<?php
get_footer();
