<?php
/**
 * Page content from the editor (regular pages and below the blocks on template pages).
 * The page title is shown in the banner (aqua_breadcrumb()).
 *
 * @package aqua
 */

?>
<section class="entry">
	<div class="container">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry__content' ); ?>>
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'large', array( 'class' => 'entry__thumbnail' ) );
			}

			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="entry__pages">Pages:',
					'after'  => '</div>',
				)
			);

			edit_post_link( 'Edit', '<p class="mt-4 mb-0">', '</p>' );
			?>
		</article>
	</div>
</section>
