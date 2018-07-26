# VU Portal Site Design Overview

The site's data and additional admin panels are managed by the **vu-panels** plugin.

The site's display is handled by the **vu_custom_portal** theme.

---

## vu-panels
*Manages the site's data and custom admin panels.*
### Files
- `js/vu-admin-scripts.js`
    - **Description:** Contains javascript functions used by the admin panel. Responsible for custom display and ajax calls not natively supported by wordpress.
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** vu-permissions.php, vu_custom_portal/functions.php (from theme)
    - **Key related functions:** 
- `js/vu-scripts.js`
    - **Description:** lorem_ipsum
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related functions:** 
- `vu-db.php`
    - **Description:** lorem_ipsum
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related functions:** 
- `vu-panels.php`
    - **Description:** lorem_ipsum
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related functions:** 
- `vu-permissions.php`
    - **Description:** Defines the base taxonomy, etc. information necessary for implementing the frontend and backend administrator panel features responsible for giving post access permissions by user group. (User groups are a new concept introduced for managing file ownership. Each post has an associated user group, and users can only edit said post if they are a member of that user group, or an admin. Users can be a member of multiple user groups)
    - **Key functions:** 
    - **Notes:** The **vu_user_group** taxonomy, like all taxonomies, creates new taxonomy terms whenever existing taxonomy terms attempt to be added to the user/post/etc, which merely reference the real thing. Call vu-util.php/vu_get_real_object_terms() to get the actual taxonomy terms attached to a post/taxonomy.
    - **Related files:** vu-alter-user-group.php, vu-add-group-for-user.php, vu.choose-user-initial-group.php
    - **Key related functions:** 
- `vu-alter-user-group.php`
    - **Description:** Allows admins to add and alter user groups from the users.php page
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** js/vu-admin-scripts.js
    - **Key related functions:** 
- `vu-change-groups-for-user.php`
    - **Description:** Allows admins to add/remove user groups for a specific user from that user's profile.php page
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related functions:** 
- `vu.choose-user-initial-group.php`
    - **Description:** Allows admins to choose the user group a newly added user will intiially be a member of.
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related functions:** 
- `vu-util.php`
    - **Description:** lorem_ipsum
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related functions:** 
- `__template__`
    - **Description:** lorem_ipsum
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related functions:** 
### Constants
- `IS_WP_DEBUG:` Returns true if wordpress is in debug mode
- `USER_GROUP_TO_ROLE` Constant portion of the name of the database table mapping vu_user_group terms to their associated roles
- `vu_permission_level::Admin` Name of the role corresponding to administrator priveleges
- `VU_USER_GROUP` Name of the taxonomy responsible for giving posts access permissions by user group
- `VU_USER_PRIMARY_UG` Metadata attached to a User object that contains the default vu_user_group term to use when creating taxonomies.


- `'vu_user_group'` Name of the taxonomy responsible for giving post access permissions by user group

vu_user_primary_ug
---
## vu_custom_portal
*Manages the display of the site, excluding admin panels. Based on wordpress's **_s (underscores)** theme. Some unimportant files have been omitted.*
### Files
- `better-font-awesome-library/`
    - **Description:** Copy of external library for loading pretty icons and such.
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `images/`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `inc/`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `js/`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `languages/`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `layouts/`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `template-parts/`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `favicon.ico`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `front-page.php`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `functions.php`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `single.php`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `style.css`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
- `__template__`
    - **Description:** lorem_ipsum
    - **Key features:** 
    - **Notes:** 
    - **Related files:** 
    - **Key related features:** 
