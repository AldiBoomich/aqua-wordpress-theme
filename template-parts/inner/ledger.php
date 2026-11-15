<?php
/**
 * Ledger — facts or terms as an editorial list (title | text | short detail), rows separated by hairlines.
 * No icons, no cards. Used for quality & eco proof (About) and delivery terms (Products).
 *
 * Fields on the current page: {prefix}_title, {prefix}_text, {prefix}_items[title, text, detail].
 *
 * Args:
 *   prefix   (string) Field prefix and the section id, e.g. 'proof' or 'delivery'.
 *   title    (string) Default title.
 *   text     (string) Default intro.
 *   items    (array)  Default rows.
 *
 * @package aqua
 */

$aqua_prefix = sanitize_key( $args['prefix'] ?? 'ledger' );
$aqua_page   = get_queried_object_id();
$aqua_title  = aqua_field( $aqua_prefix . '_title', $args['title'] ?? '', $aqua_page );
$aqua_text   = aqua_field( $aqua_prefix . '_text', $args['text'] ?? '', $aqua_page );
$aqua_items  = array_filter(
	aqua_rows( $aqua_prefix . '_items', $args['items'] ?? array(), $aqua_page ),
	static fn( $item ) => ! empty( $item['title'] )
);
if ( ! $aqua_items ) {
	return;
}
?>
<section class="ledger ledger--<?php echo esc_attr( $aqua_prefix ); ?>" aria-labelledby="<?php echo esc_attr( $aqua_prefix ); ?>-title">
	<div class="wrap">
		<div class="ledger__head">
			<h2 class="section-title ledger__title" id="<?php echo esc_attr( $aqua_prefix ); ?>-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
			<?php if ( $aqua_text ) : ?>
				<p class="ledger__lead"><?php echo esc_html( $aqua_text ); ?></p>
			<?php endif; ?>
		</div>

		<ul class="ledger__list">
			<?php foreach ( $aqua_items as $aqua_item ) : ?>
				<li class="ledger__item">
					<h3 class="ledger__name"><?php echo esc_html( $aqua_item['title'] ); ?></h3>
					<?php if ( ! empty( $aqua_item['text'] ) ) : ?>
						<p class="ledger__text"><?php echo esc_html( $aqua_item['text'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $aqua_item['detail'] ) ) : ?>
						<p class="ledger__detail"><?php echo esc_html( $aqua_item['detail'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
