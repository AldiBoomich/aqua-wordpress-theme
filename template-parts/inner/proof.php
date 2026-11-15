<?php
/**
 * About: proof — certificates, lab checks and eco facts as an editorial ledger
 * (title | text | short fact), rows separated by hairlines. Fields: About → Quality & Eco.
 *
 * @package aqua
 */

$aqua_page  = get_queried_object_id();
$aqua_title = aqua_field( 'proof_title', 'Proof in every bottle', $aqua_page );
$aqua_text  = aqua_field( 'proof_text', 'Certificates, lab checks and reusable bottles. What we promise on the label, we can show on paper.', $aqua_page );
$aqua_items = aqua_rows(
	'proof_items',
	array(
		array(
			'title'  => 'ISO 22000 certified plant',
			'text'   => 'Our food-safety system is audited every year, from the spring to the sealed cap.',
			'detail' => 'Audited yearly',
		),
		array(
			'title'  => 'Every batch lab-tested',
			'text'   => 'An independent lab checks each batch for microbiology, minerals and taste before it leaves the plant.',
			'detail' => '40+ markers',
		),
		array(
			'title'  => 'Reusable 19 L bottles',
			'text'   => 'Large bottles are washed, inspected and refilled. Worn ones are recycled into new bottles.',
			'detail' => 'Up to 50 refills',
		),
		array(
			'title'  => 'Cleaner delivery routes',
			'text'   => 'Routes are planned to avoid empty runs, and our city vans are electric.',
			'detail' => 'Electric city vans',
		),
	),
	$aqua_page
);
$aqua_items = array_filter( $aqua_items, static fn( $item ) => ! empty( $item['title'] ) );
if ( ! $aqua_items ) {
	return;
}
?>
<section class="proof" aria-labelledby="proof-title">
	<div class="wrap">
		<div class="proof__head">
			<h2 class="section-title proof__title" id="proof-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
			<?php if ( $aqua_text ) : ?>
				<p class="proof__lead"><?php echo esc_html( $aqua_text ); ?></p>
			<?php endif; ?>
		</div>

		<ul class="proof__list">
			<?php foreach ( $aqua_items as $aqua_item ) : ?>
				<li class="proof__item">
					<h3 class="proof__name"><?php echo esc_html( $aqua_item['title'] ); ?></h3>
					<?php if ( ! empty( $aqua_item['text'] ) ) : ?>
						<p class="proof__text"><?php echo esc_html( $aqua_item['text'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $aqua_item['detail'] ) ) : ?>
						<p class="proof__detail"><?php echo esc_html( $aqua_item['detail'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
