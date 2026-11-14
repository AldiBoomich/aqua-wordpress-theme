<?php
/**
 * Post card (journal, blog archive, related posts). The whole card is one link (stretched title link);
 * the photo zooms gently on hover.
 *
 * Args: large — the first, bigger card.
 *
 * @package aqua
 */

$aqua_large = ! empty( $args['large'] );
$aqua_cats  = get_the_category();
?>
<article <?php post_class( 'journal-card' . ( $aqua_large ? ' journal-card--large' : '' ) ); ?> data-reveal="fade">
	<div class="journal-card__media">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail(
				'1536x1536',
				array(
					'class' => 'journal-card__image',
					'sizes' => $aqua_large ? '(min-width: 62em) 58vw, 100vw' : '(min-width: 62em) 30vw, 100vw',
					'alt'   => '',
				)
			);
		}
		?>
	</div>
	<div class="journal-card__body">
		<p class="journal-card__meta">
			<?php if ( $aqua_cats ) : ?>
				<span><?php echo esc_html( $aqua_cats[0]->name ); ?></span>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		</p>
		<h3 class="journal-card__title"><a class="journal-card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( $aqua_large ) : ?>
			<p class="journal-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?></p>
		<?php endif; ?>
	</div>
</article>
