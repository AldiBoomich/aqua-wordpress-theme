<?php
/**
 * Page head — the light title area of every inner screen (replaces the old dark photo banner).
 * The H1 words rise from masks with a pure CSS entrance; the lead and everything else are visible from the first paint.
 *
 * Args:
 *   title   (string) H1 text. Default: aqua_banner_title() (Page Head → Title, else the page title).
 *   lead    (string) Intro paragraph. Default: Page Head → Lead of the current page.
 *   variant (string) '' | 'compact' (text pages) | 'media' (photo below the title).
 *   meta    (string) Escaped HTML printed above the title (date, category…).
 *   after   (string) Escaped HTML printed under the lead (search form, buttons…).
 *   image   (mixed)  Photo for the 'media' variant (ID / ACF value). Default: Page Head → Photo.
 *
 * @package aqua
 */

$aqua_object  = is_home() ? (int) get_option( 'page_for_posts' ) : ( is_singular() ? get_queried_object_id() : 0 );
$aqua_title   = isset( $args['title'] ) ? (string) $args['title'] : aqua_banner_title();
$aqua_lead    = array_key_exists( 'lead', $args ?? array() ) ? (string) $args['lead'] : ( $aqua_object ? (string) aqua_field( 'banner_lead', '', $aqua_object ) : '' );
$aqua_variant = $args['variant'] ?? '';
$aqua_image   = $args['image'] ?? ( $aqua_object ? aqua_field( 'banner_image', 0, $aqua_object ) : 0 );
$aqua_classes = 'page-head wrap' . ( $aqua_variant ? ' page-head--' . $aqua_variant : '' );
?>
<header class="<?php echo esc_attr( $aqua_classes ); ?>">
	<div class="page-head__text">
		<?php if ( ! empty( $args['meta'] ) ) : ?>
			<p class="page-head__meta"><?php echo $args['meta']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the caller. ?></p>
		<?php endif; ?>

		<h1 class="page-head__title"><?php echo aqua_title_words( $aqua_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_title_words(). ?></h1>

		<?php if ( $aqua_lead ) : ?>
			<p class="page-head__lead"><?php echo esc_html( $aqua_lead ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $args['after'] ) ) : ?>
			<div class="page-head__after"><?php echo $args['after']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the caller. ?></div>
		<?php endif; ?>
	</div>

	<?php if ( 'media' === $aqua_variant && $aqua_image ) : ?>
		<figure class="page-head__media">
			<?php
			// The photo is the LCP element of the screen: eager, high priority.
			echo aqua_photo( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() markup.
				$aqua_image,
				'',
				array(
					'class'         => 'page-head__image',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
					'sizes'         => '(min-width: 90em) 1344px, calc(100vw - 2 * clamp(1rem, 0.4rem + 2.6vw, 3rem))',
				)
			);
			?>
		</figure>
	<?php endif; ?>
</header>
