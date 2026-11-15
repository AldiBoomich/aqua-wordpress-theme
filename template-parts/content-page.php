<?php
/**
 * Editor content under the blocks of template pages (About, Service, Products, Contact):
 * a reading column (block "prose"). The title and photo are already in the page head.
 *
 * @package aqua
 */

?>
<section <?php post_class( 'page-body page-body--extra wrap' ); ?>>
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
</section>
