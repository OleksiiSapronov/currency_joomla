INSERT IGNORE INTO `#__rsfirewall_configuration` (`name`, `value`, `type`) VALUES
('active_scanner_status', '1', 'int'),
('capture_backend_password', '1', 'int'),
('verify_upload', '1', 'int'),
('verify_upload_blacklist_exts', 'pht\r\nphp\r\njs\r\nexe\r\ncom\r\nbat\r\ncmd', 'text'),
('monitor_core', '1', 'int'),
('monitor_users', '', 'array-int'),
('active_scanner_status_backend', '1', 'int'),
('backend_password_enabled', '0', 'int'),
('backend_password_use_parameter', '0', 'int'),
('backend_password_parameter', 'password', 'text'),
('backend_password', '', 'text'),
('log_emails', '', 'text'),
('log_alert_level', '', 'array-text'),
('log_history', '30', 'int'),
('log_overview', '5', 'int'),
('verify_agents', 'perl\ncurl\njava', 'array-text'),
('verify_multiple_exts', '1', 'int'),
('capture_backend_login', '1', 'int'),
('code', '', 'text'),
('verify_generator', '1', 'int'),
('grade', '0', 'int'),
('offset', '300', 'int'),
('request_timeout', '0', 'int'),
('max_retries', '10', 'int'),
('check_md5', '1', 'int'),
('retries_timeout', '10', 'int'),
('log_system_check', '0', 'int'),
('enable_extra_logging', '0', 'int'),
('enable_backend_captcha', '0', 'int'),
('backend_captcha_font_size', '32', 'int'),
('blocked_countries', '', 'array-text'),
('autoban_attempts', '10', 'int'),
('enable_autoban', '0', 'int'),
('enable_autoban_login', '0', 'int'),
('log_hour_limit', '50', 'int'),
('log_emails_count', '0', 'int'),
('log_emails_send_after', '0', 'int'),
('lfi', '1', 'int'),
('rfi', '1', 'int'),
('enable_php_for', 'get', 'array-text'),
('enable_sql_for', 'get', 'array-text'),
('enable_js_for', 'post', 'array-text'),
('filter_js', '1', 'int'),
('filter_uploads', '1', 'int'),
('disable_installer', '0', 'int'),
('disable_new_admin_users', '0', 'int'),
('admin_users', '', 'array-int'),
('file_permissions', '644', 'int'),
('folder_permissions', '755', 'int'),
('google_safebrowsing_api_key', '', 'text'),
('google_webrisk_api_key', '', 'text'),
('google_apis', 'safebrowsing\nwebrisk', 'array-text'),
('abusive_ips', '0', 'int'),
('ipv4_whois', 'http://whois.domaintools.com/{ip}', 'text'),
('ipv6_whois', 'http://whois.domaintools.com/{ip}', 'text'),
('system_check_last_run', '', 'text'),
('deny_referer', '', 'text'),
('check_proxy_ip_headers', '', 'array-text'),
('use_joomla_ip', '1', 'int'),
('abusive_ips_checks', 'dnsbl.tornevall.org\nsbl-xbl.spamhaus.org\ndnsbl.justspam.org', 'array-text'),
('check_user_password', '1', 'int'),
('maxmind_license_key', '', 'text'),
('dot_files', '.gitignore\n.gitkeep\n.gitattributes\n.mailmap\n.php_cs.dist\n.php_cs\n.csslintrc\n.csscomb.json\n.jshintrc\n.editorconfig\n.drone.jsonnet', 'text'),
('optional_folders', 'administrator/components/com_associations\nadministrator/components/com_banners\nadministrator/components/com_contact\nadministrator/components/com_contenthistory\nadministrator/components/com_fields\nadministrator/components/com_finder\nadministrator/components/com_newsfeeds\nadministrator/components/com_search\nadministrator/components/com_weblinks\nadministrator/language/en-GB\nadministrator/modules/mod_feed\nadministrator/modules/mod_latest\nadministrator/modules/mod_latestactions\nadministrator/modules/mod_logged\nadministrator/modules/mod_menu\nadministrator/modules/mod_popular\nadministrator/modules/mod_privacy_dashboard\nadministrator/modules/mod_status\nadministrator/modules/mod_submenu\nadministrator/modules/mod_sampledata\nadministrator/modules/mod_stats_admin\nadministrator/modules/mod_title\nadministrator/modules/mod_multilangstatus\nadministrator/modules/mod_version\nadministrator/templates/atum\nadministrator/templates/bluestork\nadministrator/templates/isis\nadministrator/templates/hathor\ncomponents/com_banners\ncomponents/com_contact\ncomponents/com_contenthistory\ncomponents/com_fields\ncomponents/com_finder\ncomponents/com_newsfeeds\ncomponents/com_search\ncomponents/com_weblinks\nmedia/editors/codemirror\nmedia/editors/none\nmedia/editors/tinymce\nmedia/com_finder\nmedia/mod_sampledata\nimages/sampledata\nmodules/mod_articles_archive\nmodules/mod_articles_categories\nmodules/mod_articles_category\nmodules/mod_articles_popular\nmodules/mod_articles_latest\nmodules/mod_articles_news\nmodules/mod_banners\nmodules/mod_random_image\nmodules/mod_related_items\nmodules/mod_search\nmodules/mod_stats\nmodules/mod_weblinks\nmodules/mod_whosonline\nmodules/mod_wrapper\nmodules/mod_feed\nmodules/mod_finder\nmodules/mod_footer\nmodules/mod_tags_popular\nmodules/mod_tags_similar\nmodules/mod_users_latest\nplugins/actionlog/joomla\nplugins/content/confirmconsent\nplugins/content/contact\nplugins/content/emailcloak\nplugins/content/fields\nplugins/content/finder\nplugins/content/joomla\nplugins/content/loadmodule\nplugins/content/pagebreak\nplugins/content/pagenavigation\nplugins/content/vote\nplugins/authentication/cookie\nplugins/authentication/gmail\nplugins/authentication/ldap\nplugins/captcha/recaptcha\nplugins/captcha/recaptcha_invisible\nplugins/editors/tinymce\nplugins/editors-xtd/article\nplugins/editors-xtd/contact\nplugins/editors-xtd/fields\nplugins/editors-xtd/image\nplugins/editors-xtd/menu\nplugins/editors-xtd/module\nplugins/editors-xtd/pagebreak\nplugins/editors-xtd/readmore\nplugins/fields/calendar\nplugins/fields/checkboxes\nplugins/fields/color\nplugins/fields/editor\nplugins/fields/imagelist\nplugins/fields/integer\nplugins/fields/list\nplugins/fields/media\nplugins/fields/radio\nplugins/fields/repeatable\nplugins/fields/sql\nplugins/fields/text\nplugins/fields/textarea\nplugins/fields/url\nplugins/fields/user\nplugins/fields/usergrouplist\nplugins/finder/categories\nplugins/finder/contacts\nplugins/finder/content\nplugins/finder/newsfeeds\nplugins/finder/tags\nplugins/privacy/actionlogs\nplugins/privacy/consents\nplugins/privacy/content\nplugins/privacy/contact\nplugins/privacy/message\nplugins/privacy/user\nplugins/quickicon/privacycheck\nplugins/sampledata/blog\nplugins/search/categories\nplugins/search/contacts\nplugins/search/content\nplugins/search/newsfeeds\nplugins/search/tags\nplugins/system/actionlogs\nplugins/system/debug\nplugins/system/fields\nplugins/system/highlight\nplugins/system/languagecode\nplugins/system/logrotation\nplugins/system/p3p\nplugins/system/privacyconsent\nplugins/system/sef\nplugins/system/sessiongc\nplugins/system/stats\nplugins/system/updatenotification\nplugins/twofactorauth/totp\nplugins/twofactorauth/yubikey\nplugins/user/contactcreator\nplugins/user/profile\nplugins/user/terms\ntemplates/atomic\ntemplates/beez3\ntemplates/beez5\ntemplates/beez_20\ntemplates/cassiopeia\ntemplates/protostar', 'array-text');