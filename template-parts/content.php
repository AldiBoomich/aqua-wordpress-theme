<?php
/**
 * Post card ("post-card" block) used in the blog section, posts page and archives.
 *
 * @package aqua
 *
 * @var array $args { index: card position — used for the animation delay and fallback image }
 */

$aqua_i     = isset( $args['index'] ) ? (int) $args['index'] : 0;
$aqua_delay = sprintf( '%.2f', ( ( $aqua_i % 3 ) + 1 ) * 0.2 );
?>
<div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="<?php echo esc_attr( $aqua_delay ); ?>s">
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
		<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail(
					'medium_large',
					array(
						'class' => 'post-card__image',
						'alt'   => '',
					)
				);
			} else {
				echo '<img class="post-card__image" src="' . esc_url( aqua_img( 'blog-' . ( ( $aqua_i % 3 ) + 1 ) . '.jpg' ) ) . '" alt="" loading="lazy">';
			}
			?>
			<span class="post-card__date"><i class="fas fa-calendar-alt" aria-hidden="true"></i> <?php echo esc_html( get_the_date( 'M j Y' ) ); ?></span>
		</a>
		<div class="post-card__body">
			<h3 class="post-card__title"><a class="post-card__title-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 15 ) ); ?></p>
			<a class="post-card__more" href="<?php the_permalink(); ?>">Read More <i class="fas fa-angle-right" aria-hidden="true"></i><span class="visually-hidden">: <?php the_title(); ?></span></a>
		</div>
	</article>
</div>
