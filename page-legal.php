<?php
/**
 * Template Name: Legal
 *
 * Long legal text (Terms & Conditions, Privacy Policy…), written in the block editor.
 * Every H2 becomes a numbered section and an entry of the sticky table of contents.
 *
 * @package aqua
 */

get_header();

while ( have_posts() ) :
	the_post();

	$aqua_legal = aqua_legal_sections( apply_filters( 'the_content', get_the_content() ) );
	?>
	<article <?php post_class( 'legal' ); ?>>
		<header class="page-head page-head--compact wrap">
			<h1 class="page-head__title"><?php echo aqua_title_words( get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_title_words(). ?></h1>
			<p class="page-head__meta">
				<?php echo esc_html( aqua_label( 'updated', 'Last updated' ) ); ?>
				<time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( get_the_modified_date() ); ?></time>
			</p>
		</header>

		<div class="legal__body wrap">
			<?php if ( count( $aqua_legal['toc'] ) > 1 ) : ?>
				<nav class="legal__toc" aria-labelledby="legal-toc-title">
					<h2 class="legal__toc-title" id="legal-toc-title"><?php echo esc_html( aqua_label( 'toc', 'On this page' ) ); ?></h2>
					<ol class="legal__toc-list">
						<?php foreach ( $aqua_legal['toc'] as $aqua_id => $aqua_title ) : ?>
							<li><a class="legal__toc-link" href="#<?php echo esc_attr( $aqua_id ); ?>"><?php echo esc_html( $aqua_title ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</nav>
			<?php endif; ?>

			<div class="legal__content">
				<?php echo $aqua_legal['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output (already filtered by WordPress). ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
