<?php
/**
 * Service: for business — one deep-water panel: a title, a short text and a quote button,
 * then three plain points (cooler rental, invoices, a manager). Fields: Service → For Business.
 *
 * @package aqua
 */

$aqua_page   = get_queried_object_id();
$aqua_title  = aqua_field( 'business_title', 'Water for your office, sorted', $aqua_page );
$aqua_text   = aqua_field( 'business_text', 'One plan for the whole office: coolers, bottles and a single monthly invoice.', $aqua_page );
$aqua_items  = array_filter(
	aqua_rows(
		'business_items',
		array(
			array(
				'title' => 'Free cooler rental',
				'text'  => 'Hot and cold coolers at no cost with a monthly plan of 4+ bottles, sanitised every 3 months.',
			),
			array(
				'title' => 'One invoice a month',
				'text'  => 'Every delivery on a single company invoice, with the delivery notes attached.',
			),
			array(
				'title' => 'A personal manager',
				'text'  => 'One person who knows your office, adjusts the schedule and answers within the hour.',
			),
		),
		$aqua_page
	),
	static fn( $item ) => ! empty( $item['title'] )
);
$aqua_button = aqua_link(
	aqua_field( 'business_button', null, $aqua_page ),
	array(
		'title' => 'Get a free quote',
		'url'   => add_query_arg( 'product', rawurlencode( 'Office plan' ), aqua_page_url( 'contact' ) ),
	)
);
?>
<section class="business" aria-labelledby="business-title">
	<div class="wrap">
		<div class="business__panel">
			<div class="business__head">
				<h2 class="business__title" id="business-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
				<div class="business__aside">
					<?php if ( $aqua_text ) : ?>
						<p class="business__text"><?php echo esc_html( $aqua_text ); ?></p>
					<?php endif; ?>
					<?php echo aqua_link_tag( $aqua_button, 'button button--light' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_link_tag(). ?>
				</div>
			</div>

			<?php if ( $aqua_items ) : ?>
				<ul class="business__points">
					<?php foreach ( $aqua_items as $aqua_item ) : ?>
						<li class="business__point">
							<h3 class="business__point-title"><?php echo esc_html( $aqua_item['title'] ); ?></h3>
							<?php if ( ! empty( $aqua_item['text'] ) ) : ?>
								<p class="business__point-text"><?php echo esc_html( $aqua_item['text'] ); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
