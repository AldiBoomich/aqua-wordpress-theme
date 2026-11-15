<?php
/**
 * Blog index (posts page, categories, tags, dates, authors): page head → topic links → post grid
 * (the first card large on the first page) → pagination; nothing published → a short hint and the key pages.
 *
 * Texts: Page Head of the posts page (title, lead), category descriptions, Theme Settings → Labels.
 *
 * @package aqua
 */

$aqua_posts_page = (int) get_option( 'page_for_posts' );

if ( is_home() ) {
	$aqua_lead = $aqua_posts_page
		? aqua_field( 'banner_lead', 'Short reads on hydration, water quality and looking after your cooler.', $aqua_posts_page )
		: '';
} else {
	$aqua_lead = wp_strip_all_tags( (string) get_the_archive_description() );
}

get_template_part( 'template-parts/page-head', null, array( 'lead' => $aqua_lead ) );

$aqua_topics = get_categories(
	array(
		'hide_empty' => true,
		'orderby'    => 'name',
	)
);
$aqua_first  = max( 1, (int) get_query_var( 'paged' ) ) === 1;
?>

<?php if ( $aqua_topics ) : ?>
	<nav class="topics wrap" aria-label="<?php echo esc_attr( aqua_label( 'topics', 'Topics' ) ); ?>">
		<ul class="topics__list">
			<li class="topics__item">
				<a class="topics__link" href="<?php echo esc_url( aqua_blog_url() ); ?>"<?php echo is_home() ? ' aria-current="page"' : ''; ?>><?php echo esc_html( aqua_label( 'all_posts', 'All articles' ) ); ?></a>
			</li>
			<?php foreach ( $aqua_topics as $aqua_topic ) : ?>
				<li class="topics__item">
					<a class="topics__link" href="<?php echo esc_url( get_category_link( $aqua_topic ) ); ?>"<?php echo is_category( $aqua_topic->term_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $aqua_topic->name ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
<?php endif; ?>

<section class="journal journal--archive" aria-label="<?php echo esc_attr( aqua_banner_title() ); ?>">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="journal__grid journal__grid--archive">
				<?php
				$aqua_i = 0;
				while ( have_posts() ) :
					the_post();
					get_template_part(
						'template-parts/journal-card',
						null,
						array(
							'large' => $aqua_first && 0 === $aqua_i++,
							'tag'   => 'h2',
						)
					);
				endwhile;
				?>
			</div>

			<?php aqua_pagination(); ?>
		<?php else : ?>
			<div class="results__empty">
				<p class="results__empty-text"><?php echo esc_html( aqua_label( 'no_posts', 'No articles here yet. Start from one of these pages.' ) ); ?></p>
				<?php get_template_part( 'template-parts/helpful-links' ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
