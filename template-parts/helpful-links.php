<?php
/**
 * Key pages offered when nothing was found (404, empty search). Titles: Theme Settings → Labels.
 *
 * @package aqua
 */

?>
<nav class="helpful-links" aria-label="<?php echo esc_attr( aqua_label( 'helpful_links', 'Helpful pages' ) ); ?>">
	<ul class="helpful-links__list">
		<?php foreach ( aqua_helpful_links() as $aqua_link ) : ?>
			<li class="helpful-links__item">
				<a class="helpful-links__link" href="<?php echo esc_url( $aqua_link['url'] ); ?>">
					<span><?php echo esc_html( $aqua_link['title'] ); ?></span>
					<?php echo aqua_ui_icon( 'chevron', 'helpful-links__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
