<?php
/**
 * Block "pricing" — price list cards (homepage and Products page).
 *
 * Fields (Pages → Home → Pricing): product_subtitle, product_title, product_text,
 * product_button_text, product_note, product_items (repeater: bottle — drawn shape or "image", image, volume, title, text,
 * features — one per line, price, unit, badge, link).
 *
 * @package aqua
 */

$aqua_products = aqua_home_rows(
	'product_items',
	array(
		array(
			'image'    => 'bottles/bottle-05.svg',
			'volume'   => '0.5 L',
			'title'    => 'On-the-Go Bottle',
			'text'     => 'Handy size for the gym, school and travel.',
			'features' => "Pack of 24 bottles\nBPA-free PET\nSports cap available",
			'price'    => '$9.90',
			'unit'     => '/ 24-pack',
			'badge'    => '',
			'link'     => '',
		),
		array(
			'image'    => 'bottles/bottle-15.svg',
			'volume'   => '1.5 L',
			'title'    => 'Family Bottle',
			'text'     => 'Everyday hydration for the whole family.',
			'features' => "Pack of 6 bottles\nRecyclable packaging\nFree delivery from $20",
			'price'    => '$5.40',
			'unit'     => '/ 6-pack',
			'badge'    => '',
			'link'     => '',
		),
		array(
			'image'    => 'bottles/bottle-5.svg',
			'volume'   => '5 L',
			'title'    => 'Home Jug',
			'text'     => 'Easy-carry handle, perfect for the kitchen.',
			'features' => "Ergonomic handle\nResealable cap\nIdeal for cooking & tea",
			'price'    => '$3.20',
			'unit'     => '/ jug',
			'badge'    => '',
			'link'     => '',
		),
		array(
			'image'    => 'bottles/bottle-19.svg',
			'volume'   => '19 L',
			'title'    => 'Cooler Bottle',
			'text'     => 'The classic choice for offices and big homes.',
			'features' => "Fits any water cooler\nFree cooler rental*\nBottle exchange service",
			'price'    => '$8.90',
			'unit'     => '/ bottle',
			'badge'    => 'Popular',
			'link'     => '',
		),
	)
);

$aqua_button_text = aqua_home_field( 'product_button_text', 'Order Now' );
$aqua_intro       = aqua_home_field( 'product_text', 'Transparent prices with delivery included. No contracts — order once or subscribe and save.' );
$aqua_note        = aqua_home_field( 'product_note', '* Free cooler rental with a monthly plan of 4+ bottles. Prices include delivery within city limits.' );
$aqua_columns     = count( $aqua_products ) >= 4 ? 'col-md-6 col-xl-3' : 'col-md-6 col-xl-4';
?>
<section class="pricing" id="prices">
	<div class="container">
		<div class="section-heading wow fadeInUp" data-wow-delay="0.2s">
			<p class="section-heading__subtitle"><?php echo esc_html( aqua_home_field( 'product_subtitle', 'Price List' ) ); ?></p>
			<h2 class="section-heading__title"><?php echo esc_html( aqua_home_field( 'product_title', 'Choose Your Perfect Bottle' ) ); ?></h2>
			<?php if ( $aqua_intro ) : ?>
				<p class="section-heading__text"><?php echo esc_html( $aqua_intro ); ?></p>
			<?php endif; ?>
		</div>
		<div class="swipe row g-4 justify-content-center">
			<?php
			foreach ( $aqua_products as $aqua_i => $aqua_item ) :
				$aqua_badge    = $aqua_item['badge'] ?? '';
				$aqua_url      = ! empty( $aqua_item['link'] ) ? $aqua_item['link'] : aqua_page_url( 'contact' );
				$aqua_features = aqua_lines( $aqua_item['features'] ?? '' );
				$aqua_name     = trim( ( $aqua_item['volume'] ?? '' ) . ' ' . $aqua_item['title'] );
				?>
				<div class="<?php echo esc_attr( $aqua_columns ); ?> wow fadeInUp" data-wow-delay="<?php echo esc_attr( sprintf( '%.2f', 0.1 + ( $aqua_i % 4 ) * 0.15 ) ); ?>s">
					<article class="price-card<?php echo $aqua_badge ? ' price-card--featured' : ''; ?>">
						<?php if ( $aqua_badge ) : ?>
							<span class="price-card__badge"><?php echo esc_html( $aqua_badge ); ?></span>
						<?php endif; ?>
						<div class="price-card__media">
							<?php
							// Drawn bottle with live water, or the uploaded image when chosen in the Bottle field.
							$aqua_shape = aqua_bottle_shape( $aqua_item['bottle'] ?? 'auto', $aqua_item['volume'] ?? '' );
							if ( 'image' === $aqua_shape && ! empty( $aqua_item['image'] ) ) {
								echo aqua_image_tag( $aqua_item['image'], '', 'medium_large', array( 'class' => 'price-card__image', 'alt' => $aqua_name ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								echo aqua_bottle( 'image' === $aqua_shape ? aqua_bottle_shape( 'auto', $aqua_item['volume'] ?? '' ) : $aqua_shape, $aqua_item['volume'] ?? '', 'price-card__bottle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>
						<div class="price-card__body">
							<?php if ( ! empty( $aqua_item['volume'] ) ) : ?>
								<span class="price-card__volume"><?php echo esc_html( $aqua_item['volume'] ); ?></span>
							<?php endif; ?>
							<h3 class="price-card__title"><?php echo esc_html( $aqua_item['title'] ); ?></h3>
							<?php if ( ! empty( $aqua_item['text'] ) ) : ?>
								<p class="price-card__text"><?php echo esc_html( $aqua_item['text'] ); ?></p>
							<?php endif; ?>
							<?php if ( $aqua_features ) : ?>
								<ul class="price-card__features">
									<?php foreach ( $aqua_features as $aqua_feature ) : ?>
										<li class="price-card__feature"><i class="price-card__feature-icon fas fa-check" aria-hidden="true"></i><?php echo esc_html( $aqua_feature ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<div class="price-card__footer">
								<?php if ( ! empty( $aqua_item['price'] ) ) : ?>
									<p class="price-card__price">
										<span class="price-card__amount"><?php echo esc_html( $aqua_item['price'] ); ?></span>
										<?php if ( ! empty( $aqua_item['unit'] ) ) : ?>
											<span class="price-card__unit"><?php echo esc_html( $aqua_item['unit'] ); ?></span>
										<?php endif; ?>
									</p>
								<?php endif; ?>
								<?php if ( $aqua_button_text ) : ?>
									<a class="price-card__button button button--block <?php echo $aqua_badge ? 'button--primary' : 'button--outline'; ?>" href="<?php echo esc_url( $aqua_url ); ?>" aria-label="<?php echo esc_attr( $aqua_button_text . ': ' . $aqua_name ); ?>"><?php echo esc_html( $aqua_button_text ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					</article>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $aqua_note ) : ?>
			<p class="pricing__note"><?php echo esc_html( $aqua_note ); ?></p>
		<?php endif; ?>
	</div>
</section>
