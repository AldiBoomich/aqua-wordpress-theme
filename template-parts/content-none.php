<?php
/**
 * "Nothing found" message (inside the blog grid).
 *
 * @package aqua
 */

?>
<div class="col-12 text-center">
	<h3 class="mb-3">Nothing Found</h3>
	<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
		<p class="mb-0">Ready to publish your first post? <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">Get started here</a>.</p>
	<?php else : ?>
		<p class="mb-0">It seems we can&rsquo;t find what you&rsquo;re looking for.</p>
	<?php endif; ?>
</div>
