<?php
/**
 * Search results: a compact page head with the search form, then a plain list of posts and pages.
 * Nothing found → a short hint and the key pages. Texts: Theme Settings → Labels.
 *
 * @package aqua
 */

get_header();

$aqua_query = get_search_query();
$aqua_title = '' !== $aqua_query
	/* translators: %s: search query. */
	? sprintf( '%1$s “%2$s”', aqua_label( 'results_for', 'Results for' ), $aqua_query )
	: aqua_label( 'search', 'Search the site' );

if ( have_posts() && '' !== $aqua_query ) {
	global $wp_query;
	$aqua_count = (int) $wp_query->found_posts;
	$aqua_lead  = sprintf(
		'%1$s %2$s',
		number_format_i18n( $aqua_count ),
		1 === $aqua_count ? aqua_label( 'result_one', 'result' ) : aqua_label( 'result_many', 'results' )
	);
} else {
	$aqua_lead = '';
}

get_template_part(
	'template-parts/page-head',
	null,
	array(
		'title'   => $aqua_title,
		'lead'    => $aqua_lead,
		'variant' => 'compact',
		'after'   => get_search_form( array( 'echo' => false ) ),
	)
);
?>

<section class="results wrap" aria-label="<?php echo esc_attr( aqua_label( 'results_list', 'Search results' ) ); ?>">
	<?php if ( have_posts() && '' !== $aqua_query ) : ?>
		<ol class="results__list">
			<?php
			while ( have_posts() ) :
				the_post();
				$aqua_type = 'post' === get_post_type() ? aqua_label( 'type_post', 'Article' ) : aqua_label( 'type_page', 'Page' );
				?>
				<li class="results__item">
					<article <?php post_class( 'results__entry' ); ?>>
						<p class="results__meta">
							<span><?php echo esc_html( $aqua_type ); ?></span>
							<?php if ( 'post' === get_post_type() ) : ?>
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							<?php endif; ?>
						</p>
						<h2 class="results__title"><a class="results__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php
						$aqua_excerpt = wp_trim_words( get_the_excerpt(), 28, '…' );
						if ( $aqua_excerpt ) :
							?>
							<p class="results__excerpt"><?php echo esc_html( $aqua_excerpt ); ?></p>
						<?php endif; ?>
					</article>
				</li>
			<?php endwhile; ?>
		</ol>

		<?php aqua_pagination(); ?>
	<?php else : ?>
		<div class="results__empty">
			<p class="results__empty-text">
				<?php
				echo esc_html(
					'' !== $aqua_query
						? aqua_label( 'no_results', 'Nothing matched your search. Try a shorter word, or start from one of these pages.' )
						: aqua_label( 'search_hint', 'Type what you are looking for, or start from one of these pages.' )
				);
				?>
			</p>
			<?php get_template_part( 'template-parts/helpful-links' ); ?>
		</div>
	<?php endif; ?>
</section>

<?php
get_footer();
