Theme-Mentor-For-Themeforest
============
####Fork from [Theme-Mentor](https://github.com/mpeshev/Theme-Mentor)

Theme Mentor For Themeforest - helper plugin for themeforest WordPress Themes , the cousin of Theme-Check reporting other possible theme issues



Theme Mentor is quite similar to Theme-Check. It iterates all .php files - your root theme folder template
files and your includes in inner folders (plus functions.php).


###Currently supported validations:
* Check All dynamic data escape
* Check  TGMPA force activation and deactivation plugins
* Check dirname(__FILE__)
* Mark all tags in template files
* Warn about query_posts() usage
* capital_P_dangit control (disallow any WordPress spelling other than WordPress as is – that is WORDPRESS and WordPress, ugh)
* wp_deregister_script(‘jquery’) is forbidden
* wp_dequeue_script(‘jquery’) is forbidden
* wp_enqueue_script(‘jquery’) is loaded
* prevent global $data; call as a common troublemaker (props @pippinsplugins)

###header.php specific

* Make sure that wp_head is before
* Check Title tag available in header.php

###footer.php specific

* Make sure that wp_footer is before

![Mentor](https://raw.githubusercontent.com/Ataurr/Theme-Mentor-For-Themeforest/master/screenshot.png)


Different checks are being run to ensure the code quality of the theme. Theme-Check is more or less
trustworthy, it does report valid theme errors or missing features most of the time, but it is missing
most of the eventual issues in a theme.

What Theme Mentor does in addition is reporting everything that might or might not be suspicious. The average
success rate is about 70%, but it serves as a reminder for common WPTRT review remarks for you to double check.
After all, you don't lose anything. If you verify the report from Theme Mentor, you would either: a) confirm
that your code base is in tact, or: b) fix a nasty error that Theme-Check is afraid to report (fault tolerance issues).


####filter to exclude specific folder names from the checks.
```theme_mentory_excluded_folders```
