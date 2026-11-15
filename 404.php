<?php
/**
 * 404 page (block "not-found"): compact page head with search, a drop of living water and the key pages.
 * Texts: Theme Settings → 404 Page and → Labels.
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package aqua
 */

get_header();

get_template_part(
	'template-parts/page-head',
	null,
	array(
		'title'   => aqua_field( 'error_title', 'Page not found' ),
		'lead'    => aqua_field( 'error_text', 'This page has moved or never existed. Search the site, or start from one of the pages below.' ),
		'meta'    => esc_html( aqua_label( 'error_code', 'Error 404' ) ),
		'variant' => 'compact',
		'after'   => get_search_form( array( 'echo' => false ) ),
	)
);
?>

<section class="not-found wrap" aria-label="<?php echo esc_attr( aqua_label( 'helpful_links', 'Helpful pages' ) ); ?>">
	<?php echo aqua_icon( 'fas fa-tint', 'not-found__drop' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_icon(). ?>
	<div class="not-found__body">
		<?php get_template_part( 'template-parts/helpful-links' ); ?>
		<a class="button button--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( aqua_label( 'back_home', 'Back to homepage' ) ); ?></a>
	</div>
</section>

<?php
get_footer();
