<?php
/**
 * The template for displaying image attachments
 *
 * @package Apostrophe
 */

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
				// Start the loop.
			while ( have_posts() ) : the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="entry-meta"><?php apostrophe_posted_on(); ?></div>
					<?php edit_post_link( __( 'Edit', 'apostrophe' ), '<span class="edit-link">', '</span>' ); ?>
					<h1 class="entry-title"><?php the_title(); ?></h1>

				<div class="entry-content">

					<div class="entry-attachment">
						<?php
							// Use a the large image size
							$image_size = apply_filters( 'apostrophe_attachment_size', 'large' );
							echo wp_get_attachment_image( get_the_ID(), $image_size );
						?>

						<?php if ( has_excerpt() ) : ?>
								<div class="entry-caption">
									<?php the_excerpt(); ?>
								</div><!-- .entry-caption -->
							<?php endif; ?>

						</div><!-- .entry-attachment -->

						<?php
						the_content();
						wp_link_pages( array(
							'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'apostrophe' ) . '</span>',
							'after'       => '</div>',
							'link_before' => '<span>',
							'link_after'  => '</span>',
							'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'apostrophe' ) . ' </span>%',
							'separator'   => '<span class="screen-reader-text">, </span>',
						) );
						?>
					</div><!-- .entry-content -->

					<footer class="entry-footer">
						<?php if ( $post->post_parent ) { ?>
							<nav id="image-navigation" class="navigation image-navigation">
								<div class="nav-previous"><?php previous_image_link( false, __( '&laquo; Previous Image', 'apostrophe' ) ); ?></div><div class="nav-next"><?php next_image_link( false, __( 'Next Image &raquo;', 'apostrophe' ) ); ?></div>
						</nav><!-- .image-navigation -->
						<?php } ?>

						<?php
						// Show a link back to post
						the_post_navigation( array(
							'prev_text' => _x( '<span class="meta-nav">Published in</span><span class="post-title">%title</span>', 'Parent post link', 'apostrophe' ),
						) );
						?>

					</footer><!-- .entry-footer -->

				</article><!-- #post-## -->

				<?php
				// If comments are open or we have at least one comment, load up the comment template
				if ( comments_open() || get_comments_number() ) :
					comments_template();
					endif;

				// End the loop.
				endwhile;
			?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
