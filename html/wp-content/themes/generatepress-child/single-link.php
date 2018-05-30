<?php
/**
 * The Template for displaying all single posts.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if(function_exists(vu_log)){vu_log("single-link.php  get_header()");}
else{$message = "ERROR: vu_log function DNE";
	echo "<script type='text/javascript'>alert('$message');</script>";}

get_header(); ?>

	<div id="primary" <?php generate_content_class();?>>
		<main id="main" <?php generate_main_class(); ?>>
			<?php
			/**
			 * generate_before_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_before_main_content' );

			while ( have_posts() ) : the_post();

				$link = get_post_meta(get_the_ID(), 'link_url_value', true); ?>

				<h2><?php the_title(); ?></h2>
				
				<!-- <a class='content' href="<?php $link ?>" > <?php the_title(); ?> </a> -->
				<div class='content'>
					<?php the_title("<a href='$link'>","</a>");
					the_content() ?>
				</div>
				<?php
				echo "TEST";
				vu_log("single-link.php the_post()");
				get_template_part( 'content', 'single' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || '0' != get_comments_number() ) :
					/**
					 * generate_before_comments_container hook.
					 *
					 * @since 2.1
					 */
					do_action( 'generate_before_comments_container' );
					?>

					<div class="comments-area">
						<?php comments_template(); ?>
					</div>

					<?php
				endif;

			endwhile;

			/**
			 * generate_after_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_after_main_content' );
			?>
		</main>
	</div>

	<?php
	/**
	 * generate_after_primary_content_area hook.
	 *
	 * @since 2.0
	 */
	 do_action( 'generate_after_primary_content_area' );

	 generate_construct_sidebars();

get_footer();
?>