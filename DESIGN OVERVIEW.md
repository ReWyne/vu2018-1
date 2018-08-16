# VU Portal Site Design Overview

The site's data and additional admin panels are managed by the **vu-panels** plugin.

The site's display is handled by the **vu_custom_portal** theme.

---

## vu-panels
*Manages the site's data and custom admin panels.* 
*No external requirements, but the **WP PHP Console** plugin is recommended.*
### Files
- `vu-db.php`
    - **Description:** Manages the database portion of the site.
    - **Notes:** Currently only used to map vu_user_group taxonomy terms to their associated roles. Remember to change the $vu_db_version global if you modify any code here, so that the database can update itself.
- `vu-util.php`
    - **Description:** Provides various utility functions not explicitly associated with a particular file or data item. This includes various debugging functions and functions useful for working with taxonomies.
    - **Notes:**
        - By default, vu_dbg() outputs both to wp-content/debug.log and your web browser's console. The latter requires setting up the WP PHP Console plugin. If you do not want to need this plugin installed, call vu_log() instead.
        - Several debugging-related globals are defined at the top of the file.
        - The most useful functions provided are: vu_dbg(), vu_is_custom_post_type(), vu_terms_array_to_set(),  vu_get_set_intersection(), vu_get_real_object_terms(), and vu_get_real_terms()
- `vu-panels.php`
    - **Description:** The first file called in the plugin. Defines various constants, initalizes the link custom post type, adds a bit of custom css, and adds a few functions that affect to that custom post type:
        - **vu_mark_CPTs()** and **category_id_class()** add some additional classes to our posts, for frontend display purposess.
        - **vu_generate_link_posts()** replaces the posts which are usually printed to the website's front page with the link custom post type.
        - **vu_custom_admin_css** adds css that, along with a few other minor changes, makes the Role select in users' profile pages unselectable.
    - **Notes:** Primary debugging-related globals are also set here. As the name suggests, vu_mark_CPTs() only affects custom post types, while category_id_class() affects all post types.
- `js/vu-admin-scripts.js`
    - **Description:** Contains custom javascript functions used by the admin panel. 
    - **Notes:** Currently only used to allow vu-alter-user-group.php to properly submit its data to the backend, and return the backend's response. Javascript is enqueued in vu-permissions.php -> vu_selectively_enqueue_admin_scripts()
- `vu-permissions.php`
    - **Description:** Defines the base taxonomy, etc. information necessary for implementing the frontend and backend administrator panel features responsible for giving post access permissions by user group. (User groups are a new concept introduced for managing file ownership. Each post has an associated user group, and users can only edit said post if they are a member of that user group, or an admin. Users can be a member of multiple user groups) Only users with VU_Department or Admin permissions have any access to the admin panel. Also responsible for restricting access to posts that the user does not have permission to edit.
    - **Notes:** 
        - The **vu_user_group** taxonomy, like all taxonomies, creates new taxonomy terms whenever existing taxonomy terms attempt to be added to the user/post/etc, which merely reference the real thing. Call vu-util.php/vu_get_real_object_terms() to get the actual taxonomy terms attached to a post/taxonomy.
        - **custom_post_listing()** currently prevents viewing (but not editing, if a post is accessed directly via URL) of any posts via the admin panel if the user is not an admin. This is to discourage non-admins from using the default post type. However, it does not outright prevent non-admins from creating new posts via the UI, or editing them via URL, as previously mentioned. If the former is not desired, conditionally call remove_menu_page( 'edit.php?post_type=post' ), or modify the capabilities of the VU_Department role appropriately. If the latter is not desired, modify vu_post_group_access_handler().
        - **vu-alter-user-group.php**, **vu-add-group-for-user.php**, and **vu-add-group-for-user.php** all depend on this file.
        - The most useful functions provided are: vu_get_user_role(), vu_get_accesible_user_groups(), and vu_get_object_user_group_intersection(). **vu_get_accesible_user_groups()** should *always* be used when determining what user groups a particular user has access to. **vu_get_object_user_group_intersection()** is particularly useful for determing if a user should have access to a particualar post.
        - vu_post_group_access_handler() and custom_post_listing() are responsible for restricting access to uneditable user groups. 
- `vu-alter-user-group.php`
    - **Description:** Allows admins to add and alter user groups from the users.php page
    - **Notes:** 
        - This is the only file requiring js/vu-admin-scripts.js
        - Remember that re-adding a user group with the same name as a previous one can change the role associated with that user group.
- `vu-change-groups-for-user.php`
    - **Description:** Allows admins to add/remove user groups for a specific user from that user's profile.php page
    - **Notes:** The user's role is computed automatically based on the highest privelege level among their  current user groups. This supercedes attempts to change the permissions of a user directly by modifying the Role select on their user page. (the Role select still displays the correct role for that user)
- `vu-change-groups-for-user.php`
    - **Description:** Allows admins to change the user group of a specific post or link from that post's edit page.
    - **Notes:** The Department select defaults to the current vu_user_group (department) in charge of managing that post.
- `vu-filter-by-user-group.php`
    - **Description:** Implements the dropdown that allows posts or links to be filtered by user group in edit.php
    - **Notes:** This file only implements the dropdown used for filtering. The VU User Group column itself uses the premade wordpress option `'show_admin_column' => true` in vu-permissions.php's register_taxonomy call. As the dropdown uses some regex to display correctly, it is comparatively brittle, but is presence is not required for the column itself to work correctly.
### Important Constants
- `IS_WP_DEBUG:` Returns true if wordpress is in debug mode
- `USER_GROUP_TO_ROLE` Constant portion of the name of the database table mapping vu_user_group terms to their associated roles
- `vu_permission_level::Admin` Name of the role corresponding to administrator priveleges
- `vu_permission_level::Department` Name of the role corresponding to vu_department priveleges
- `vu_permission_level::Basic` Name of the role corresponding to subscriber priveleges
- `VU_USER_GROUP` Name of the taxonomy responsible for giving posts access permissions by user group
- `VU_USER_PRIMARY_UG` Metadata attached to a User object that contains the default vu_user_group term to use when creating taxonomies.
- `$vu_panels_vars['RESTRICT_DEBUG_LEVEL']` Global used in conjuction with the function VU_RESTRICT_DEBUG_LEVEL( $level )  to limit how much debug information is printed by degrees. Setting this global to 0 will print everything, whereas setting it to 5 will only print a select few very readable debug messages.

---

## vu_custom_portal
*Manages the display of the site, excluding admin panels. Based on wordpress's **_s (underscores)** theme. Some unimportant files have been omitted.*
*Requires **vu_panels** to function correctly.*
### Files
- `template-parts/`
    - **Description:** PHP related to displaying frontend content. content-link.php is used to display the link custom post type.
- `favicon.ico`
    - **Description:** Favicon for this website.
- `functions.php`
    - **Description:** Where various scripts, css, js, the better font awesome library, etc., are added to wordpress. The vast majority of this code is provided by underscores itself, and the vu-panels plugin does not require this file (or any file from this theme) to be loaded in order to run.
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `template-tags.php`
    - **Description:** Has code around line 137 responsible for displaying the "Featured Image" associated with each link, but otherwise unmodified compared to the underscores base version. https://codex.wordpress.org/Template_Tags
- `style.css`
    - **Description:** A large amount of css provided by underscores to bring different web browsers closer into alignment in how they printed the web page.
    - **Notes:** CSS after line 1000 is custom CSS, and is responsible for most of the theme's aesthetic.
- `sidebar.php`
    - **Description:** Displays the sidebar.
    - **Notes:** A return statement was added at the beginning of this code to prevent the sidebar from displaying, but it was otherwise unmodified.
- `images/`
    - **Description:** Includes needed images uploaded for this theme. Currently this is only **default_image.png**.
- `uploads/`
    - **Description:** Various files (only images, currently) uploaded to wordpress using the admin panel.
    - **Notes:** Removing this folder will not break the theme.
- `better-font-awesome-library/`
    - **Description:** Copy of external library for loading pretty icons and such.
    - **Notes:** Unmodified.
- `inc/`
    - **Description:** Some useful extra code provided by the underscores theme, such as code allowing for infinite scroll.
    - **Notes:** Unmodified.
- `js/`
    - **Description:** Some useful javascript provided by the underscores theme, such as enhancements to wordpress's theme customizer.
    - **Notes:** Unmodified.
- `languages/`
    - **Description:** Provided by the underscores theme. For use in translating this theme to other languages.
    - **Notes:** Unmodified.
- `layouts/`
    - **Description:** Provided by the underscored theme. Makes the sidebar a little prettier via css.
    - **Notes:** Unmodified.
