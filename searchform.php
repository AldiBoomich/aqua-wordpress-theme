<?php
/**
 * Search form (block "search-form"), used by get_search_form(): search results, 404, empty archives.
 * Texts: Theme Settings → Labels.
 *
 * @package aqua
 */

$aqua_id = wp_unique_id( 'search-' );
?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="search-form__label" for="<?php echo esc_attr( $aqua_id ); ?>"><?php echo esc_html( aqua_label( 'search', 'Search the site' ) ); ?></label>
	<div class="search-form__row">
		<?php echo aqua_ui_icon( 'search', 'search-form__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
		<input class="search-form__input" id="<?php echo esc_attr( $aqua_id ); ?>" type="search" name="s"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php echo esc_attr( aqua_label( 'search_placeholder', 'Bottles, delivery, filters…' ) ); ?>"
			autocomplete="off" enterkeyhint="search">
		<button class="button button--primary search-form__submit" type="submit"><?php echo esc_html( aqua_label( 'search_submit', 'Search' ) ); ?></button>
	</div>
</form>
