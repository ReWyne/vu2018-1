<?php
	/**
 * @package Apostrophe
 *
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 */

/*
 * Return the post format, linked to the post format archive
 * Shamelessly borrowed from Scrawl theme.
 */
function apostrophe_post_format() {
	$format = get_post_format();
	$formats = get_theme_support( 'post-formats' );

	// If the post has no format, or if it's not a format supported by the theme, return early
	if ( ! $format || ! has_post_format( $formats[0] ) ) {
		return; }

	printf( '<a class="entry-format format-%1$s" href="%2$s" title="%3$s"><span class="screen-reader-text">%1$s</span></a>',
		esc_html( strtolower( get_post_format_string( $format ) ) ),
		esc_url( get_post_format_link( $format ) ),
		sprintf( esc_html( 'All %s posts', 'apostrophe' ), esc_html( get_post_format_string( $format ) ) )
	);
}


if ( ! function_exists( 'apostrophe_post_nav' ) ) :
	/**
 * Display navigation to next/previous post when applicable.
 *
 * @return void
 */
	function apostrophe_post_nav() {
		// Don't print empty markup if there's nowhere to navigate.
		$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
		$next     = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous ) {
			return;
		}
		?>
		<nav class="navigation post-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Post navigation', 'apostrophe' ); ?></h1>
		<div class="nav-links">
			<div class="nav-previous">
			<?php previous_post_link( '%link', '<span class="meta-nav">'._x( 'Previous', 'Previous post link', 'apostrophe' ).'</span> <span class="apostrophe-post-title">%title</span>' ); ?>
			</div>
			<div class="nav-next">
				<?php next_post_link( '%link', '<span class="meta-nav">'._x( 'Next', 'Next post link', 'apostrophe' ).'</span> <span class="apostrophe-post-title">%title</span>' ); ?>
			</div>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
	}
endif;

if ( ! function_exists( 'apostrophe_posted_on' ) ) :
	/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
	function apostrophe_posted_on() {
		$human_time = apostrophe_human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) );
		$regular_time = get_the_time( get_option( 'date_format' ) );

		$output_time = sprintf( '%s <span style="display:none;">%s</span>', esc_html( $human_time ), esc_html( $regular_time ) );

		if ( current_time( 'timestamp' ) > get_the_time( 'U' ) + 60 * 60 * 24 * 14 ) {
			$output_time = esc_html( $regular_time );
		}

		printf( '<a class="entry-date published" href="%s">%s</a>', esc_url( get_permalink() ), $output_time ); // WPCS: XSS OK.
		printf( '<time class="updated" datetime="%s">%s</time>', esc_attr( get_the_modified_date( 'c' ) ), esc_html( get_the_modified_date() ) );
		printf( '<span class="byline vcard"><a class="url fn n" href="%s">%s</a></span>', esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ), get_the_author() );
	}
endif;

if ( ! function_exists( 'apostrophe_human_time_diff' ) ) :
	/**
 * Same as core's human_time_diff(), only in the "ago" context,
 * which is different for some languages.
 *
 * @since Apostrophe 1.9
 *
 * @param int $from Unix timestamp from which the difference begins.
 * @param int $to Optional. Unix timestamp to end the time difference. Default becomes time() if not set.
 * @return string Human readable time difference.
 */
	function apostrophe_human_time_diff( $from, $to = '' ) {
		if ( empty( $to ) ) {
			$to = time(); }

		$diff = (int) abs( $to - $from );

		if ( $diff < HOUR_IN_SECONDS ) {
			$mins = round( $diff / MINUTE_IN_SECONDS );
			if ( $mins <= 1 ) {
				$mins = 1; }
			/* translators: min=minute */
			$since = sprintf( _n( '%s min ago', '%s mins ago', $mins, 'apostrophe' ), $mins );
		} elseif ( $diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS ) {
			$hours = round( $diff / HOUR_IN_SECONDS );
			if ( $hours <= 1 ) {
				$hours = 1; }
			$since = sprintf( _n( '%s hour ago', '%s hours ago', $hours, 'apostrophe' ), $hours );
		} elseif ( $diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS ) {
			$days = round( $diff / DAY_IN_SECONDS );
			if ( $days <= 1 ) {
				$days = 1; }
			$since = sprintf( _n( '%s day ago', '%s days ago', $days, 'apostrophe' ), $days );
		} elseif ( $diff < 30 * DAY_IN_SECONDS && $diff >= WEEK_IN_SECONDS ) {
			$weeks = round( $diff / WEEK_IN_SECONDS );
			if ( $weeks <= 1 ) {
				$weeks = 1; }
			$since = sprintf( _n( '%s week ago', '%s weeks ago', $weeks, 'apostrophe' ), $weeks );
		} elseif ( $diff < YEAR_IN_SECONDS && $diff >= 30 * DAY_IN_SECONDS ) {
			$months = round( $diff / ( 30 * DAY_IN_SECONDS ) );
			if ( $months <= 1 ) {
				$months = 1; }
			$since = sprintf( _n( '%s month ago', '%s months ago', $months, 'apostrophe' ), $months );
		} elseif ( $diff >= YEAR_IN_SECONDS ) {
			$years = round( $diff / YEAR_IN_SECONDS );
			if ( $years <= 1 ) {
				$years = 1; }
			$since = sprintf( _n( '%s year ago', '%s years ago', $years, 'apostrophe' ), $years );
		}

		return $since;
	}
endif; // function_exists

if ( ! function_exists( 'apostrophe_entry_footer' ) ) :
	/**
 * Prints HTML with meta information for the categories, tags and comments.
 * Also prints a card with the author's bio.
 */
	function apostrophe_entry_footer() {

		// Show categories and tags if we're on a post page
		if ( 'post' === get_post_type() ) {
			$categories_list = get_the_category_list();
			if ( $categories_list && apostrophe_categorized_blog() ) {
				$apostrophe_tags = $categories_list;
			}

			$tags_list = get_the_tag_list( '<ul class="post-tags"><li>', '</li><li>', '</li></ul>' );
			if ( $tags_list && ! is_wp_error( $tags_list ) ) {
				$apostrophe_tags .= $tags_list;
			}

			if ( $apostrophe_tags ) {
				echo '<div class="apostrophe-tags">' . wp_kses_post( $apostrophe_tags ) . '</div>';
			}
		}

		// Show the author's information, only if this blog has multiple authors
		// hide = 1 means I've checked "hide author bio", and the below shouldn't display
		if ( is_multi_author() && 1 != get_theme_mod( 'apostrophe_hide_author' ) ) :
		?>
		<div class="author vcard">
		<?php echo get_avatar( get_the_author_meta( 'ID' ), 300 ); ?>
			<h3><a class="url fn n" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a></h3>
			<p class="author-bio"><?php echo wp_kses_post( get_the_author_meta( 'description', get_the_author_meta( 'ID' ) ) ); ?></p>
		</div><!-- .author -->
		<?php
		endif;
	}
endif;

if ( ! function_exists( 'the_archive_title' ) ) :
	/**
 * Shim for `the_archive_title()`.
 *
 * Display the archive title based on the queried object.
 *
 * @todo Remove this function when WordPress 4.3 is released.
 *
 * @param string $before Optional. Content to prepend to the title. Default empty.
 * @param string $after  Optional. Content to append to the title. Default empty.
 */
	function the_archive_title( $before = '', $after = '' ) {
		if ( is_category() ) {
			$title = sprintf( __( 'Category: %s', 'apostrophe' ), single_cat_title( '', false ) );
		} elseif ( is_tag() ) {
			$title = sprintf( __( 'Tag: %s', 'apostrophe' ), single_tag_title( '', false ) );
		} elseif ( is_author() ) {
			$title = sprintf( __( 'Author: %s', 'apostrophe' ), '<span class="vcard">' . get_the_author() . '</span>' );
		} elseif ( is_year() ) {
			$title = sprintf( __( 'Year: %s', 'apostrophe' ), get_the_date( _x( 'Y', 'yearly archives date format', 'apostrophe' ) ) );
		} elseif ( is_month() ) {
			$title = sprintf( __( 'Month: %s', 'apostrophe' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'apostrophe' ) ) );
		} elseif ( is_day() ) {
			$title = sprintf( __( 'Day: %s', 'apostrophe' ), get_the_date( _x( 'F j, Y', 'daily archives date format', 'apostrophe' ) ) );
		} elseif ( is_tax( 'post_format', 'post-format-aside' ) ) {
			$title = _x( 'Asides', 'post format archive title', 'apostrophe' );
		} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
			$title = _x( 'Galleries', 'post format archive title', 'apostrophe' );
		} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
			$title = _x( 'Images', 'post format archive title', 'apostrophe' );
		} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
			$title = _x( 'Videos', 'post format archive title', 'apostrophe' );
		} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
			$title = _x( 'Quotes', 'post format archive title', 'apostrophe' );
		} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
			$title = _x( 'Links', 'post format archive title', 'apostrophe' );
		} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
			$title = _x( 'Statuses', 'post format archive title', 'apostrophe' );
		} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
			$title = _x( 'Audio', 'post format archive title', 'apostrophe' );
		} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
			$title = _x( 'Chats', 'post format archive title', 'apostrophe' );
		} elseif ( is_post_type_archive() ) {
			$title = sprintf( __( 'Archives: %s', 'apostrophe' ), post_type_archive_title( '', false ) );
		} elseif ( is_tax() ) {
			$tax = get_taxonomy( get_queried_object()->taxonomy );
			/* translators: 1: Taxonomy singular name, 2: Current taxonomy term */
			$title = sprintf( __( '%1$s: %2$s', 'apostrophe' ), $tax->labels->singular_name, single_term_title( '', false ) );
		} else {
			$title = __( 'Archives', 'apostrophe' );
		}

		/**
	 * Filter the archive title.
	 *
	 * @param string $title Archive title to be displayed.
	 */
		$title = apply_filters( 'get_the_archive_title', $title );

		if ( ! empty( $title ) ) {
			echo $before . $title . $after; // WPCS: XSS OK.
		}
	}
endif;

if ( ! function_exists( 'the_archive_description' ) ) :
	/**
 * Shim for `the_archive_description()`.
 *
 * Display category, tag, or term description.
 *
 * @todo Remove this function when WordPress 4.3 is released.
 *
 * @param string $before Optional. Content to prepend to the description. Default empty.
 * @param string $after  Optional. Content to append to the description. Default empty.
 */
	function the_archive_description( $before = '', $after = '' ) {
		$description = apply_filters( 'get_the_archive_description', term_description() );

		if ( ! empty( $description ) ) {
			/**
		 * Filter the archive description.
		 *
		 * @see term_description()
		 *
		 * @param string $description Archive description to be displayed.
		 */
			echo $before . $description . $after; // WPCS: XSS OK.
		}
	}
endif;

/**
 * Returns true if a blog has more than 1 category.
 */
function apostrophe_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so apostrophe_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so apostrophe_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in apostrophe_categorized_blog.
 */
function apostrophe_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'apostrophe_category_transient_flusher' );
add_action( 'save_post',     'apostrophe_category_transient_flusher' );

if ( ! function_exists( 'apostrophe_paging_nav' ) ) :
	/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @return void
 */
	function apostrophe_paging_nav() {
		// Don't print empty markup if there's only one page.
		if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
			return;
		}

		$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$query_args   = array();
		$url_parts    = explode( '?', $pagenum_link );

		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}

		$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
		$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

		$format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

		// If the current locale uses RTL, our arrows need to point the other way
		if ( is_rtl() ) :
			$apostrophe_prev_text = __( '&rarr;', 'apostrophe' );
			$apostrophe_next_text = __( '&larr;', 'apostrophe' );
			else :
				$apostrophe_prev_text = __( '&larr;', 'apostrophe' );
				$apostrophe_next_text = __( '&rarr;', 'apostrophe' );
		endif;

			// Set up paginated links.
			$links = paginate_links( array(
				'base'      => $pagenum_link,
				'format'    => $format,
				'total'     => $GLOBALS['wp_query']->max_num_pages,
				'current'   => $paged,
				'mid_size'  => 1,
				'add_args'  => array_map( 'urlencode', $query_args ),
				'prev_text' => $apostrophe_prev_text,
				'next_text' => $apostrophe_next_text,
			) );

			if ( $links ) :

			?>
			<nav class="navigation paging-navigation" role="navigation">
			<h1 class="screen-reader-text"><?php esc_html_e( 'Posts navigation', 'apostrophe' ); ?></h1>
		<div class="pagination loop-pagination">
			<?php echo wp_kses_post( $links ); ?>
		</div><!-- .pagination -->
		</nav><!-- .navigation -->
		<?php
	endif;
	}
endif;
