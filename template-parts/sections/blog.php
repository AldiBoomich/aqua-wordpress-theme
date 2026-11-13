<?php
/**
 * Block "blog".
 * Homepage: the latest posts (blog_count). Posts page / archives: the main query + pagination.
 * Without any posts: demo cards on the homepage, a "nothing found" message in listings.
 *
 * Fields (Pages → Home → Blog): blog_subtitle, blog_title, blog_count.
 *
 * @package aqua
 */

$aqua_is_listing = is_home() || is_archive() || is_search();

if ( $aqua_is_listing ) {
	global $wp_query;
	$aqua_query = $wp_query;
} else {
	$aqua_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => min( 6, max( 1, (int) aqua_home_field( 'blog_count', 3 ) ) ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}
?>
<section class="blog" id="blog">
	<div class="container">
		<div class="section-heading wow fadeInUp" data-wow-delay="0.2s">
			<p class="section-heading__subtitle"><?php echo esc_html( aqua_home_field( 'blog_subtitle', 'Our Blog' ) ); ?></p>
			<h2 class="section-heading__title"><?php echo esc_html( aqua_home_field( 'blog_title', 'Tips & News About Healthy Hydration' ) ); ?></h2>
		</div>
		<div class="<?php echo esc_attr( $aqua_is_listing ? 'row g-4 justify-content-center' : 'swipe row g-4 justify-content-center' ); ?>">
			<?php
			if ( $aqua_query->have_posts() ) :
				$aqua_i = 0;
				while ( $aqua_query->have_posts() ) :
					$aqua_query->the_post();
					get_template_part( 'template-parts/content', null, array( 'index' => $aqua_i ) );
					$aqua_i++;
				endwhile;
				wp_reset_postdata();
			elseif ( $aqua_is_listing ) :
				get_template_part( 'template-parts/content', 'none' );
			else :
				// Demo cards while the blog has no posts yet.
				$aqua_demo = array(
					array( 'How Much Water Should You Really Drink a Day?', 'Simple rules to stay hydrated at work, at home and during workouts.' ),
					array( 'Spring vs. Tap Water: What Is the Difference?', 'We compare minerals, taste and safety — the results may surprise you.' ),
					array( '5 Signs It Is Time to Change Your Water Cooler', 'Keep your office cooler clean, safe and working like new.' ),
				);
				foreach ( $aqua_demo as $aqua_i => $aqua_post ) :
					?>
					<div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="<?php echo esc_attr( sprintf( '%.2f', ( $aqua_i + 1 ) * 0.2 ) ); ?>s">
						<article class="post-card">
							<div class="post-card__media">
								<img class="post-card__image" src="<?php echo esc_url( aqua_img( 'blog-' . ( $aqua_i + 1 ) . '.jpg' ) ); ?>" alt="" loading="lazy">
								<span class="post-card__date"><i class="fas fa-calendar-alt" aria-hidden="true"></i> <?php echo esc_html( wp_date( 'M j Y' ) ); ?></span>
							</div>
							<div class="post-card__body">
								<h3 class="post-card__title"><?php echo esc_html( $aqua_post[0] ); ?></h3>
								<p class="post-card__excerpt"><?php echo esc_html( $aqua_post[1] ); ?></p>
							</div>
						</article>
					</div>
					<?php
				endforeach;
			endif;
			?>
		</div>

		<?php
		if ( $aqua_is_listing && $aqua_query->max_num_pages > 1 ) :
			$aqua_pages = paginate_links(
				array(
					'type'      => 'array',
					'prev_text' => '<i class="fas fa-angle-left" aria-hidden="true"></i><span class="visually-hidden">Previous</span>',
					'next_text' => '<i class="fas fa-angle-right" aria-hidden="true"></i><span class="visually-hidden">Next</span>',
				)
			);
			if ( $aqua_pages ) :
				?>
				<nav aria-label="Posts navigation">
					<ul class="pagination">
						<?php foreach ( $aqua_pages as $aqua_page ) : ?>
							<li class="pagination__item">
								<?php
								$aqua_class = false !== strpos( $aqua_page, 'current' ) ? 'pagination__link pagination__link--current' : 'pagination__link';
								echo str_replace( 'class="', 'class="' . $aqua_class . ' ', $aqua_page ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() output.
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
				<?php
			endif;
		endif;
		?>
	</div>
</section>
