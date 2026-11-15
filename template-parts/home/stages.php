<?php
/**
 * Home: "From source to glass" — the 7 purification stages.
 * Desktop with motion: the scene is pinned and the stages follow the scroll: clear water fills a
 * line-drawn filter column, the impurities of each stage dissolve, the spring photo turns into the glass.
 * Phones / reduced motion / no JS: the same content as a plain numbered list (src/js/home/stages.js).
 * Args: variant — 'lite' (About page): the title beside the numbered list, no photos, no pinned scene; a thin line
 * beside the list fills with water as it scrolls through (CSS scroll-driven animation, static where unsupported).
 *
 * @package aqua
 */

$aqua_title  = aqua_home_field( 'stages_title', 'From source to glass' );
$aqua_text   = aqua_home_field( 'stages_text', 'Seven stages between the mountain spring and your glass. Every batch is checked against 40+ quality markers before bottling.' );
$aqua_source = aqua_home_field( 'stages_source_image', 0 );
$aqua_glass  = aqua_home_field( 'stages_glass_image', 0 );
$aqua_stages = aqua_home_rows(
	'stages_items',
	array(
		array(
			'title'  => 'Sediment pre-filter',
			'text'   => 'A fine mesh catches sand, silt and rust before anything else touches the water.',
			'detail' => '5 µm',
		),
		array(
			'title'  => 'Granular activated carbon',
			'text'   => 'Loose carbon granules absorb chlorine, odours and organic compounds.',
			'detail' => 'Chlorine and odour',
		),
		array(
			'title'  => 'Carbon block',
			'text'   => 'Pressed carbon polishes the water and holds back the finest particles the granules let through.',
			'detail' => '1 µm',
		),
		array(
			'title'  => 'Reverse osmosis membrane',
			'text'   => 'Water is pressed through a membrane so fine that dissolved salts, metals and microbes stay behind.',
			'detail' => '0.0001 µm',
		),
		array(
			'title'  => 'Post-carbon filter',
			'text'   => 'A last pass through coconut carbon rounds off the taste after the membrane.',
			'detail' => 'Taste',
		),
		array(
			'title'  => 'UV sterilisation',
			'text'   => 'Ultraviolet light neutralises any remaining bacteria and viruses, with no chemicals added.',
			'detail' => 'UV-C 254 nm',
		),
		array(
			'title'  => 'Remineralisation',
			'text'   => 'Calcium and magnesium are returned in balanced amounts for a soft, natural taste.',
			'detail' => 'Ca and Mg',
		),
	)
);
$aqua_stages = array_values( array_filter( $aqua_stages, static fn( $s ) => ! empty( $s['title'] ) ) );
$aqua_count  = count( $aqua_stages );
if ( ! $aqua_count ) {
	return;
}

if ( 'lite' === ( $args['variant'] ?? '' ) ) :
	?>
	<section class="stages stages--lite" aria-labelledby="stages-title">
		<div class="stages__lite wrap">
			<div class="stages__head">
				<h2 class="section-title" id="stages-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
				<?php if ( $aqua_text ) : ?>
					<p class="stages__lead"><?php echo esc_html( $aqua_text ); ?></p>
				<?php endif; ?>
			</div>
			<ol class="stages__list">
				<?php foreach ( $aqua_stages as $aqua_i => $aqua_stage ) : ?>
					<li class="stages__item">
						<p class="stages__count"><span class="stages__num"><?php echo (int) ( $aqua_i + 1 ); ?></span></p>
						<h3 class="stages__title"><?php echo esc_html( $aqua_stage['title'] ); ?></h3>
						<?php if ( ! empty( $aqua_stage['text'] ) ) : ?>
							<p class="stages__text"><?php echo esc_html( $aqua_stage['text'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $aqua_stage['detail'] ) ) : ?>
							<p class="stages__detail"><?php echo esc_html( $aqua_stage['detail'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>
	<?php
	return;
endif;

// Filter column drawing (160 × 560 grid): one segment per stage, a few impurity dots in each.
$aqua_top    = 40;
$aqua_height = 480;
$aqua_seg    = $aqua_height / $aqua_count;
?>
<section class="stages" aria-labelledby="stages-title" data-stages style="--stages-count: <?php echo (int) $aqua_count; ?>">
	<div class="stages__head wrap">
		<h2 class="section-title" id="stages-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
		<?php if ( $aqua_text ) : ?>
			<p class="stages__lead" data-reveal="fade"><?php echo esc_html( $aqua_text ); ?></p>
		<?php endif; ?>
	</div>

	<div class="stages__scene" data-stages-scene>
		<div class="stages__stage wrap">
			<div class="stages__photos" aria-hidden="true">
				<figure class="stages__photo stages__photo--source">
					<?php echo aqua_photo( $aqua_source, '', array( 'sizes' => '(min-width: 62em) 30vw, 100vw', 'alt' => '' ), 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</figure>
				<figure class="stages__photo stages__photo--glass">
					<?php echo aqua_photo( $aqua_glass, '', array( 'sizes' => '(min-width: 62em) 30vw, 100vw', 'alt' => '' ), 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</figure>
			</div>

			<div class="stages__column" aria-hidden="true">
				<svg class="stages__svg" viewBox="0 0 160 560" preserveAspectRatio="xMidYMid meet" focusable="false">
					<defs>
						<clipPath id="stages-tube"><rect x="44" y="<?php echo (int) $aqua_top; ?>" width="72" height="<?php echo (int) $aqua_height; ?>" rx="36"/></clipPath>
					</defs>
					<g clip-path="url(#stages-tube)">
						<rect class="stages__water" x="44" y="<?php echo (int) $aqua_top; ?>" width="72" height="<?php echo (int) $aqua_height; ?>"/>
						<rect class="stages__clear" data-stages-clear x="44" y="<?php echo (int) $aqua_top; ?>" width="72" height="<?php echo (int) $aqua_height; ?>"/>
						<?php
						for ( $aqua_i = 0; $aqua_i < $aqua_count; $aqua_i++ ) :
							$aqua_y0 = $aqua_top + $aqua_i * $aqua_seg;
							?>
							<g class="stages__dirt" data-stages-dirt>
								<?php
								for ( $aqua_d = 0; $aqua_d < 5 - min( 4, (int) floor( $aqua_i * 0.7 ) ); $aqua_d++ ) :
									// Deterministic scatter (same on every load).
									$aqua_x = 56 + ( ( $aqua_i * 37 + $aqua_d * 23 ) % 48 );
									$aqua_y = $aqua_y0 + 12 + ( ( $aqua_i * 19 + $aqua_d * 29 ) % max( 8, (int) $aqua_seg - 24 ) );
									$aqua_r = 1.4 + ( ( $aqua_i + $aqua_d ) % 3 ) * 0.5;
									?>
									<circle cx="<?php echo esc_attr( $aqua_x ); ?>" cy="<?php echo esc_attr( round( $aqua_y, 1 ) ); ?>" r="<?php echo esc_attr( $aqua_r ); ?>"/>
								<?php endfor; ?>
							</g>
						<?php endfor; ?>
					</g>
					<rect class="stages__tube" x="44" y="<?php echo (int) $aqua_top; ?>" width="72" height="<?php echo (int) $aqua_height; ?>" rx="36"/>
					<?php for ( $aqua_i = 1; $aqua_i < $aqua_count; $aqua_i++ ) : ?>
						<line class="stages__divider" x1="44" x2="116" y1="<?php echo esc_attr( round( $aqua_top + $aqua_i * $aqua_seg, 1 ) ); ?>" y2="<?php echo esc_attr( round( $aqua_top + $aqua_i * $aqua_seg, 1 ) ); ?>"/>
					<?php endfor; ?>
					<?php for ( $aqua_i = 0; $aqua_i < $aqua_count; $aqua_i++ ) : ?>
						<text class="stages__tick" data-stages-tick x="132" y="<?php echo esc_attr( round( $aqua_top + ( $aqua_i + 0.5 ) * $aqua_seg + 4, 1 ) ); ?>"><?php echo (int) ( $aqua_i + 1 ); ?></text>
					<?php endfor; ?>
				</svg>
			</div>

			<ol class="stages__list">
				<?php foreach ( $aqua_stages as $aqua_i => $aqua_stage ) : ?>
					<li class="stages__item" data-stage>
						<p class="stages__count"><span class="stages__num"><?php echo (int) ( $aqua_i + 1 ); ?></span><span class="stages__of">/ <?php echo (int) $aqua_count; ?></span></p>
						<h3 class="stages__title"><?php echo esc_html( $aqua_stage['title'] ); ?></h3>
						<?php if ( ! empty( $aqua_stage['text'] ) ) : ?>
							<p class="stages__text"><?php echo esc_html( $aqua_stage['text'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $aqua_stage['detail'] ) ) : ?>
							<p class="stages__detail"><?php echo esc_html( $aqua_stage['detail'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>

			<div class="stages__progress" aria-hidden="true"><span class="stages__progress-bar" data-stages-progress></span></div>
		</div>
	</div>
</section>
