<?php
/**
 * Single blog post (block "entry"). The title is shown in the page banner.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package aqua
 */

get_header();
?>

<section class="entry">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-10">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'large', array( 'class' => 'entry__thumbnail' ) );
						}
						?>

						<div class="entry__meta">
							<span><i class="entry__meta-icon fas fa-calendar-alt" aria-hidden="true"></i><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'M j Y' ) ); ?></time></span>
							<?php
							$aqua_categories = get_the_category_list( ', ' );
							if ( $aqua_categories ) :
								?>
								<span><i class="entry__meta-icon fas fa-folder" aria-hidden="true"></i><?php echo wp_kses_post( $aqua_categories ); ?></span>
							<?php endif; ?>
						</div>

						<div class="entry__content">
							<?php
							the_content();

							wp_link_pages(
								array(
									'before' => '<div class="entry__pages">Pages:',
									'after'  => '</div>',
								)
							);
							?>
						</div>

						<?php the_tags( '<p class="entry__tags"><i class="entry__meta-icon fas fa-tags" aria-hidden="true"></i>', ', ', '</p>' ); ?>
					</article>

					<?php
					$aqua_prev = get_previous_post();
					$aqua_next = get_next_post();
					if ( $aqua_prev || $aqua_next ) :
						?>
						<nav class="entry__nav" aria-label="Post navigation">
							<div>
								<?php if ( $aqua_prev ) : ?>
									<a class="button button--secondary" href="<?php echo esc_url( get_permalink( $aqua_prev ) ); ?>"><i class="fas fa-angle-left" aria-hidden="true"></i><?php echo esc_html( get_the_title( $aqua_prev ) ); ?></a>
								<?php endif; ?>
							</div>
							<div>
								<?php if ( $aqua_next ) : ?>
									<a class="button button--secondary" href="<?php echo esc_url( get_permalink( $aqua_next ) ); ?>"><?php echo esc_html( get_the_title( $aqua_next ) ); ?><i class="fas fa-angle-right" aria-hidden="true"></i></a>
								<?php endif; ?>
							</div>
						</nav>
					<?php endif; ?>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
