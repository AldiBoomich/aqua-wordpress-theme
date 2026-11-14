<?php
/**
 * Home: bottles shelf (Price List fields).
 * Desktop with motion: the section is pinned; the title stays on the left while the bottles slide past with the scroll
 * (src/js/home/shelf.js). Phones / no JS / reduced motion: a native horizontal scroller with snap points.
 *
 * @package aqua
 */

$aqua_products = aqua_home_rows(
	'product_items',
	array(
		array(
			'bottle'   => 'auto',
			'volume'   => '0.5 L',
			'title'    => 'On-the-Go Bottle',
			'text'     => 'Handy size for the gym, school and travel.',
			'features' => "Pack of 24 bottles\nBPA-free PET\nSports cap available",
			'price'    => '$9.90',
			'unit'     => '/ 24-pack',
			'link'     => '',
		),
		array(
			'bottle'   => 'auto',
			'volume'   => '1.5 L',
			'title'    => 'Family Bottle',
			'text'     => 'Everyday hydration for the whole family.',
			'features' => "Pack of 6 bottles\nRecyclable packaging\nFree delivery from $20",
			'price'    => '$5.40',
			'unit'     => '/ 6-pack',
			'link'     => '',
		),
		array(
			'bottle'   => 'auto',
			'volume'   => '5 L',
			'title'    => 'Home Jug',
			'text'     => 'Easy-carry handle, perfect for the kitchen.',
			'features' => "Ergonomic handle\nResealable cap\nIdeal for cooking & tea",
			'price'    => '$3.20',
			'unit'     => '/ jug',
			'link'     => '',
		),
		array(
			'bottle'   => 'auto',
			'volume'   => '19 L',
			'title'    => 'Cooler Bottle',
			'text'     => 'The classic choice for offices and big homes.',
			'features' => "Fits any water cooler\nFree cooler rental*\nBottle exchange service",
			'price'    => '$8.90',
			'unit'     => '/ bottle',
			'link'     => '',
		),
	)
);
$aqua_title  = aqua_home_field( 'product_title', 'Choose your bottle' );
$aqua_text   = aqua_home_field( 'product_text', 'Transparent prices with delivery included. No contracts — order once or subscribe and save.' );
$aqua_note   = aqua_home_field( 'product_note', '* Free cooler rental with a monthly plan of 4+ bottles. Prices include delivery within city limits.' );
$aqua_button = aqua_home_field( 'product_button_text', 'Order water' );
?>
<section class="shelf" aria-labelledby="shelf-title" data-shelf>
	<div class="shelf__pin" data-shelf-pin>
		<div class="shelf__layout">
			<div class="shelf__intro">
				<h2 class="section-title" id="shelf-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
				<?php if ( $aqua_text ) : ?>
					<p class="shelf__lead" data-reveal="fade"><?php echo esc_html( $aqua_text ); ?></p>
				<?php endif; ?>
				<?php if ( $aqua_note ) : ?>
					<p class="shelf__note" data-reveal="fade"><?php echo esc_html( $aqua_note ); ?></p>
				<?php endif; ?>
			</div>
			<div class="shelf__viewport" data-shelf-viewport>
				<ul class="shelf__track" data-shelf-track>
				<?php
				foreach ( $aqua_products as $aqua_item ) :
					$aqua_shape    = aqua_bottle_shape( $aqua_item['bottle'] ?? 'auto', $aqua_item['volume'] ?? '' );
					$aqua_features = aqua_lines( $aqua_item['features'] ?? '' );
					$aqua_url      = ! empty( $aqua_item['link'] ) ? $aqua_item['link'] : aqua_page_url( 'contact' );
					$aqua_name     = trim( ( $aqua_item['volume'] ?? '' ) . ' ' . ( $aqua_item['title'] ?? '' ) );
					?>
					<li class="shelf__item">
						<div class="shelf__bottle">
							<?php
							if ( 'image' === $aqua_shape && ! empty( $aqua_item['image'] ) ) {
								echo aqua_photo( $aqua_item['image'], '', array( 'class' => 'shelf__image', 'sizes' => '20rem', 'alt' => $aqua_name ), 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								echo aqua_bottle( 'image' === $aqua_shape ? 'cooler' : $aqua_shape, $aqua_item['volume'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_bottle().
							}
							?>
						</div>
						<div class="shelf__info">
							<p class="shelf__volume"><?php echo esc_html( $aqua_item['volume'] ?? '' ); ?></p>
							<h3 class="shelf__title"><?php echo esc_html( $aqua_item['title'] ?? '' ); ?></h3>
							<?php if ( ! empty( $aqua_item['text'] ) ) : ?>
								<p class="shelf__text"><?php echo esc_html( $aqua_item['text'] ); ?></p>
							<?php endif; ?>
							<?php if ( $aqua_features ) : ?>
								<ul class="shelf__features">
									<?php foreach ( $aqua_features as $aqua_feature ) : ?>
										<li><?php echo esc_html( $aqua_feature ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<div class="shelf__buy">
								<p class="shelf__price"><span class="shelf__amount"><?php echo esc_html( $aqua_item['price'] ?? '' ); ?></span> <span class="shelf__unit"><?php echo esc_html( $aqua_item['unit'] ?? '' ); ?></span></p>
								<a class="button button--secondary button--small" href="<?php echo esc_url( $aqua_url ); ?>"><?php echo esc_html( $aqua_button ); ?><span class="screen-reader-text">: <?php echo esc_html( $aqua_name ); ?></span></a>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="shelf__progress wrap" aria-hidden="true"><span class="shelf__progress-bar" data-shelf-progress></span></div>
	</div>
</section>
