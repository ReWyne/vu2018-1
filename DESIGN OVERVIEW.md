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
    - **Description:** Defines the base taxonomy, etc. information necessary for implementing the frontend and backend administrator panel features responsible for giving post access permissions by user group
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** vu-alter-user-group.php, vu-add-group-for-user.php, vu.choose-user-initial-group.php
    - **Key related functions:** 
- `vu-alter-user-group.php`
    - **Description:** Allows admins to add and alter user groups from the users.php page
    - **Key functions:** 
    - **Notes:** 
    - **Related files:** js/vu-admin-scripts.js
    - **Key related functions:** 
- `vu-add-group-for-user.php`
    - **Description:** Allows admins to add user groups to a specific user from that user's profile.php page
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