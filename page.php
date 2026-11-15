<?php
/**
 * Regular page (default template): compact page head + the editor content in a reading column (block "prose").
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package aqua
 */

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/page-head', null, array( 'variant' => 'compact' ) );
	?>
	<article <?php post_class( 'page-body wrap' ); ?>>
		<?php
		if ( has_post_thumbnail() ) {
			echo '<figure class="page-body__media">';
			the_post_thumbnail(
				'aqua-2560',
				array(
					'class'   => 'page-body__image',
					'sizes'   => '(min-width: 90em) 1344px, 100vw',
					'loading' => 'eager',
				)
			);
			echo '</figure>';
		}
		?>
		<div class="prose">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<nav class="prose__pages" aria-label="' . esc_attr( aqua_label( 'pagination', 'Pages' ) ) . '">',
					'after'  => '</nav>',
				)
			);
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
