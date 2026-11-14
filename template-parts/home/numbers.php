<?php
/**
 * Home: numbers — four large figures that count up once when they come into view.
 * Every number, suffix and label is editable (Home → Stats). The real figure is printed by PHP,
 * so it is correct without JavaScript and for reduced motion.
 *
 * @package aqua
 */

$aqua_title = aqua_home_field( 'stats_title', 'Trusted by thousands every day' );
$aqua_items = aqua_home_rows(
	'stats_items',
	array(
		array(
			'number' => 15,
			'suffix' => '',
			'label'  => 'years of clean water',
		),
		array(
			'number' => 12000,
			'suffix' => '+',
			'label'  => 'homes and offices served',
		),
		array(
			'number' => 85,
			'suffix' => 'K',
			'label'  => 'bottles delivered every month',
		),
		array(
			'number' => 24,
			'suffix' => '',
			'label'  => 'cities on our routes',
		),
	)
);
?>
<section class="numbers" aria-labelledby="numbers-title">
	<div class="wrap">
		<h2 class="section-title numbers__title" id="numbers-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
		<dl class="numbers__list">
			<?php
			foreach ( $aqua_items as $aqua_item ) :
				$aqua_number = (int) preg_replace( '/\D/', '', (string) ( $aqua_item['number'] ?? 0 ) );
				?>
				<div class="numbers__item" data-reveal="fade">
					<dt class="numbers__label"><?php echo esc_html( $aqua_item['label'] ?? '' ); ?></dt>
					<dd class="numbers__value">
						<span data-count="<?php echo esc_attr( $aqua_number ); ?>"><?php echo esc_html( number_format_i18n( $aqua_number ) ); ?></span><?php echo esc_html( $aqua_item['suffix'] ?? '' ); ?>
					</dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>
