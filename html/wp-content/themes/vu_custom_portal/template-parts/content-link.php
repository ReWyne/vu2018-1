<?php
/**
 * Template part for displaying links
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package vu_custom_portal
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php vu_custom_portal_post_thumbnail(); ?>
	<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;
		vu_pc_debug("content-link echoing");
		?>
		<a class="entry-link" href="<?php echo esc_url( get_post_meta($post->ID, "link_url_value", true) ) . '" rel="external">'; ?></a>
		<?php
		//echo '<a class="entry-link" href="' . esc_url( get_post_meta($post->ID, "link_url_value", true) ) . '" rel="external">'.'</a>';
		if ( 'link' === get_post_type() && is_singular() ) :
			?>
			<div class="entry-meta">
				<?php
				vu_custom_portal_posted_on();
				vu_custom_portal_posted_by();
				?>
			</div><!-- .entry-meta -->
		<?php endif; ?>
	</header><!-- .entry-header -->

	

	<div class="entry-content">
		<?php
		the_content( sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'vu_custom_portal' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			get_the_title()
		) );

		wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'vu_custom_portal' ),
			'after'  => '</div>',
		) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php vu_custom_portal_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
