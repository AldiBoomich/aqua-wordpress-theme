<?php
/**
 * Single blog post: compact page head (topic, date, reading time, title, excerpt) → wide photo →
 * the text in a reading column (block "prose") → tags → previous / next article → more articles →
 * the closing call to order. Texts: Theme Settings → Labels; the call to order: Home → Order Call.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package aqua
 */

get_header();

while ( have_posts() ) :
	the_post();

	$aqua_cats = get_the_category();
	$aqua_meta = '';
	if ( $aqua_cats ) {
		$aqua_meta .= '<a class="page-head__meta-link" href="' . esc_url( get_category_link( $aqua_cats[0] ) ) . '">' . esc_html( $aqua_cats[0]->name ) . '</a>';
	}
	$aqua_meta .= '<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>';
	$aqua_meta .= '<span>' . esc_html( number_format_i18n( aqua_read_time() ) . ' ' . aqua_label( 'read_time', 'min read' ) ) . '</span>';

	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'meta'    => $aqua_meta,
			'lead'    => has_excerpt() ? get_the_excerpt() : '',
			'variant' => 'compact',
		)
	);
	?>

	<article <?php post_class( 'page-body page-body--post wrap' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="page-body__media page-body__media--wide">
				<?php
				// The photo is the LCP element of the post: eager, high priority.
				the_post_thumbnail(
					'aqua-2560',
					array(
						'class'         => 'page-body__image',
						'sizes'         => '(min-width: 90em) 1344px, calc(100vw - 2 * clamp(1rem, 0.4rem + 2.6vw, 3rem))',
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'decoding'      => 'async',
					)
				);
				?>
			</figure>
		<?php endif; ?>

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

		<?php
		$aqua_tags = get_the_tags();
		if ( $aqua_tags ) :
			?>
			<div class="post-tags">
				<h2 class="post-tags__title"><?php echo esc_html( aqua_label( 'tags', 'Tags' ) ); ?></h2>
				<ul class="post-tags__list">
					<?php foreach ( $aqua_tags as $aqua_tag ) : ?>
						<li><a class="text-link" href="<?php echo esc_url( get_tag_link( $aqua_tag ) ); ?>"><?php echo esc_html( $aqua_tag->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</article>

	<?php
	$aqua_prev = get_previous_post();
	$aqua_next = get_next_post();
	if ( $aqua_prev || $aqua_next ) :
		?>
		<nav class="post-nav wrap" aria-label="<?php echo esc_attr( aqua_label( 'post_nav', 'More from the journal' ) ); ?>">
			<div class="post-nav__grid">
				<?php if ( $aqua_prev ) : ?>
					<a class="post-nav__link post-nav__link--prev" href="<?php echo esc_url( get_permalink( $aqua_prev ) ); ?>" rel="prev">
						<span class="post-nav__label"><?php echo esc_html( aqua_label( 'prev_post', 'Previous article' ) ); ?></span>
						<span class="post-nav__title"><?php echo esc_html( get_the_title( $aqua_prev ) ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $aqua_next ) : ?>
					<a class="post-nav__link post-nav__link--next" href="<?php echo esc_url( get_permalink( $aqua_next ) ); ?>" rel="next">
						<span class="post-nav__label"><?php echo esc_html( aqua_label( 'next_post', 'Next article' ) ); ?></span>
						<span class="post-nav__title"><?php echo esc_html( get_the_title( $aqua_next ) ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</nav>
	<?php endif; ?>

	<?php
	// More articles: the latest posts except this one.
	$aqua_more = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 3,
			'post__not_in'        => array( get_the_ID() ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( $aqua_more->have_posts() ) :
		?>
		<section class="journal journal--related" aria-labelledby="more-title">
			<div class="wrap">
				<div class="journal__head">
					<h2 class="section-title" id="more-title" data-reveal="lines"><?php echo esc_html( aqua_label( 'more_articles', 'More articles' ) ); ?></h2>
					<a class="text-link journal__all" href="<?php echo esc_url( aqua_blog_url() ); ?>"><?php echo esc_html( aqua_label( 'all_posts', 'All articles' ) ); ?></a>
				</div>
				<div class="journal__grid journal__grid--row" style="--cards: <?php echo (int) $aqua_more->post_count; ?>">
					<?php
					while ( $aqua_more->have_posts() ) :
						$aqua_more->the_post();
						get_template_part(
							'template-parts/journal-card',
							null,
							array( 'sizes' => 2 === $aqua_more->post_count ? '(min-width: 48em) 48vw, 100vw' : '(min-width: 62em) 30vw, (min-width: 48em) 48vw, 100vw' )
						);
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_template_part( 'template-parts/home/order' );

get_footer();
