<?php
	/**
 * @package Apostrophe
 *
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	/*
	* If our posts have featured images, we'll show them in the grid.
	* Otherwise, we'll fall back to a grey box with an icon representing the post format.
	*/
	if ( get_the_post_thumbnail() ) :
		// Use a larger image if the post is featured.
		if ( true === apostrophe_is_featured() ) {
			$apostrophe_post_thumbnail = get_the_post_thumbnail( $post->ID, 'apostrophe-featured' );
		} else {
			$apostrophe_post_thumbnail = get_the_post_thumbnail();
		}
		$apostrophe_has_thumbnail = 'apostrophe-thumb';
	else :
		$apostrophe_post_thumbnail = '<span></span>';
		$apostrophe_has_thumbnail = 'apostrophe-nothumb';
	endif; // check for post thumbnail

		/*
		* If the post format is a link, we want to link directly to that link, rather than to the post itself
		*/
	if ( 'link' === get_post_format() ) :
		$apostrophe_permalink = apostrophe_get_url();
	else :
		$apostrophe_permalink = get_permalink();
	endif;
	?>

	<a class="entry-thumbnail <?php echo esc_attr( $apostrophe_has_thumbnail ); ?>" href="<?php echo esc_url( $apostrophe_permalink ); ?>"><?php echo wp_kses_post( $apostrophe_post_thumbnail ); ?></a>
  <header class="entry-header">
		<?php apostrophe_inline_controls(); ?>
    <div class="entry-meta">
		<?php apostrophe_posted_on(); ?>
    </div><!-- .entry-meta -->
		<h2 class="entry-title"><a href="<?php echo esc_url( $apostrophe_permalink ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
	</header><!-- .entry-header -->

</article><!-- #post-## -->
