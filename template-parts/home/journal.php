<?php
/**
 * Home: journal — latest posts, one large and the rest small. Fields: Home → Blog.
 *
 * @package aqua
 */

$aqua_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => min( 6, max( 1, (int) aqua_home_field( 'blog_count', 3 ) ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( ! $aqua_query->have_posts() ) {
	return;
}
$aqua_link_text = aqua_home_field( 'blog_link_text', 'All articles' );
?>
<section class="journal" aria-labelledby="journal-title">
	<div class="wrap">
		<div class="journal__head">
			<h2 class="section-title" id="journal-title" data-reveal="lines"><?php echo esc_html( aqua_home_field( 'blog_title', 'Tips and news about healthy hydration' ) ); ?></h2>
			<a class="text-link journal__all" href="<?php echo esc_url( aqua_blog_url() ); ?>"><?php echo esc_html( $aqua_link_text ); ?></a>
		</div>
		<div class="journal__grid">
			<?php
			$aqua_i = 0;
			while ( $aqua_query->have_posts() ) :
				$aqua_query->the_post();
				// Small cards here are a thumbnail beside the text on desktop (~16vw), two columns on tablets.
				$aqua_large = 0 === $aqua_i++;
				get_template_part(
					'template-parts/journal-card',
					null,
					array(
						'large' => $aqua_large,
						'sizes' => $aqua_large ? '(min-width: 62em) 58vw, 100vw' : '(min-width: 62em) 17vw, (min-width: 48em) 48vw, 100vw',
					)
				);
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
