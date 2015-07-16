<?php
/*
 * English language file
 */

return [
    /* InstallerException.php */
    'rewrite_key_not_exist' => 'Unable to rewrite key "%s" in config, does it exist?',
    'rewrite_failed' => 'Unable to rewrite key "%s" in config, rewrite failed',

    /* Installer.php */
    'invalid_handler' => 'Invalid handler: %s',
    'specify_database_host' => 'Please specify a database host',
    'specify_database_name' => 'Please specify the database name',
    'connection_failed' => 'Connection failed: ',
    'database_not_empty' => 'Database "%s" is not empty. Please empty the database or specify another database.',
    'specify_admin_first_name' => 'Please specify the administrator first name',
    'specify_admin_last_name' => 'Please specify the administrator last name',
    'specify_admin_email' => 'Please specify administrator email address',
    'specify_valid_email' => 'Please specify valid email address',
    'specify_admin_password' => 'Please specify password',
    'confirm_admin_password' => 'Please confirm chosen password',
    'specify_password_not_match_confirm' => 'Specified password does not match the confirmed password',
    'specify_encryption_key' => 'Please specify encryption key',
    'specify_valid_key' => 'The encryption key should be of a valid length (%s).',
    'specify_folder_permission_mask' => 'Please specify folder permission mask',
    'specify_file_permission_mask' => 'Please specify file permission mask',
    'specify_folder_valid_mask' => 'Please specify a valid folder permission mask',
    'specify_file_valid_mask' => 'Please specify a valid file permission mask',
    'plugin_download_failed' => 'Plugin download failed, missing name',
    'theme_download_failed' => 'Theme download failed, missing name',
    'unable_open_core' => 'Unable to open application archive file',
    'unable_extract_core' => 'Could not extract application files',
    'unable_open_plugin' => 'Unable to open plugin archive file',
    'unable_open_theme' => 'Unable to open theme archive file',
    'unable_find_autoloader' => 'Unable to find autoloader: ~/bootstrap/autoload.php',
    'unable_find_app_loader' => 'Unable to find app loader: ~/bootstrap/app.php',
    'server_responded_error' => 'Server responded with error: ',
    'server_no_response' => 'Server responded had no response.',
    'server_invalid_response' => 'Server returned an invalid response.',
    'failed_create_temporary_directory' => 'Failed to get create temporary directory in %s. Please ensure this directory is writable.',
    'failed_deliver_package' => 'Server failed to deliver the package',
    'corrupt_package' => 'Package files from server are corrupt',

    /* install.php */
    'title' => 'October Installation',
    'system_check_title' => 'System Check',
    'system_check_next' => 'Agree & Continue',
    'system_check_php_version' => 'PHP version 5.4 or greater required',
    'system_check_required_curl' => 'cURL PHP Extension is required',
    'system_check_test_connection' => 'Test connection to the installation server',
    'system_check_write_permission' => 'Permission to write to directories and files',
    'system_check_write_permission_reason' => 'The installer was unable to write to the installation directories and files.',
    'system_check_required_pdo' => 'PDO PHP Extension is required',
    'system_check_required_mcrypt' => 'MCrypt PHP Extension is required',
    'system_check_required_mbstring' => 'Mbstring PHP Extension is required',
    'system_check_required_openssl' => 'OpenSSL PHP Extension is required',
    'system_check_required_zip' => 'ZipArchive PHP Library is required',
    'system_check_required_gd' => 'GD PHP Library is required',
    'config_form_title' => 'Configuration',
    'config_form_next' => 'Continue',
    'config_form_database_label' => 'Database',
    'config_form_database_category' => 'General',
    'config_form_admin_label' => 'Administrator',
    'config_form_admin_category' => 'General',
    'config_form_advanced_label' => 'Advanced',
    'config_form_advanced_category' => 'Advanced',
    'starter_form_title' => 'Getting started',
    'themes_form_title' => 'Start from a theme',
    'project_form_title' => 'Project details',
    'project_form_next' => 'Install!',
    'project_form_project' => 'Project',
    'project_form_plugins' => 'Plugins',
    'project_form_themes' => 'Themes',
    'install_progress_title' => 'Installation progress...',
    'install_progress_get_meta_data' => 'Requesting package information',
    'install_progress_download_core' => 'Downloading application files',
    'install_progress_download_plugins' => 'Downloading plugin: ',
    'install_progress_download_themes' => 'Downloading theme: ',
    'install_progress_extract_core' => 'Unpacking application files',
    'install_progress_extract_plugins' => 'Unpacking plugin: ',
    'install_progress_extract_themes' => 'Unpacking theme: ',
    'install_progress_setup_config' => 'Building configuration files',
    'install_progress_create_admin' => 'Create admin account',
    'install_progress_setup_project' => 'Setting website project',
    'install_progress_finish_install' => 'Finishing installation',
    'install_complete_title' => 'Congratulations!',

    /* title.htm */

    /* themes.htm */
    'html_themes_lead' => 'Loading themes...',
    'html_themes_text_center' => 'Themes can install plugins and create pages needed to jump start your website.',

    /* starter.htm */
    'html_starter_text_center' => 'How do you want to set up your site?',
    'html_starter_start_scratch' => 'Start from scratch',
    'html_starter_scratch_p1' => 'Install October without any plugins or themes. This is a good option to choose for custom building.',
    'html_starter_scratch_p2' => 'You\'ll need to code your site in HTML / CSS.',
    'html_starter_start_theme' => 'Start from a theme',
    'html_starter_theme_p1' => 'Pick from a pre-built site that fits a general purpose and you can customize later.',
    'html_starter_theme_p2' => 'For example: A blog site, a portfolio site.',
    'html_starter_start_project' => 'Use a project ID',
    'html_starter_project_p1' => 'If you\'ve set up a project at the OctoberCMS website you can enter it here.',
    'html_starter_project_p2' => 'This option can be used to define plugins and themes manually.',

    /* project.htm */
    'html_project_h3' => 'Custom install',
    'html_project_p1' => 'Instead of providing a project ID, you can define plugins and themes manually using the links above.',
    'html_project_p2' => 'When finished, click Install to continue.',

    /* progress.htm */

    /* header.htm */
    'html_header_h1' => 'October',

    /* footer.htm */

    /* config.htm */

    /* complete.htm */
    'html_complete_lead' => 'Installation has been successfully completed',
    'html_complete_app_h4' => 'Website address',
    'html_complete_app_p' => 'Your website is located at this URL:',
    'html_complete_app_h4_2' => 'Administration Area',
    'html_complete_app_p_2' => 'Use the following link to log into the administration area:',
    'html_complete_post_h4' => 'Post-install configuration',
    'html_complete_post_p' => 'Now that we\'re done, there a few things you may want to address for the smooth operation of your site. Please review the ' .
'<a href="http://octobercms.com/docs/help/installation#post-install-config" target="_blank">installation help guide</a> for further instructions.',
    'html_complete_clean_h4' => 'Important!',
    'html_complete_clean_p' => 'For security reasons you should delete the installation files, the <strong>install.php</strong> script and the <strong>install_files</strong> directory.',

    /* check.htm */
    'html_check_h3' => 'License agreement',
    'html_check_p1' => 'MIT license',
    'html_check_p2' => 'Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:',
    'html_check_p3' => 'The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.',
    'html_check_p4' => 'THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.',

    /* themes/theme.htm */
    'html_themes_theme_span' => 'Install this?',
    'html_themes_theme_confirm' => 'Confirm',
    'html_themes_theme_cancel' => 'Cancel',
    'html_themes_theme_demo' => 'Demo',
    'html_themes_theme_details' => 'Details',
    'html_themes_theme_install' => 'Install',

    /* project/themes.htm */
    'html_project_themes_lead' => 'Choose some starter themes to include (optional).',
    'html_project_themes_search' => 'search themes...',
    'html_project_themes_included' => 'Included',
    'html_project_themes_no_included' => 'There are no themes included.',
    'html_project_themes_recommended' => 'Recommended',
    'html_project_themes_included_project' => 'These themes are included with the project',

    /* project/theme.htm */

    /* project/suggestion.htm */

    /* project/project.htm */
    'html_project_project_lead' => 'If you have a Project for this installation, specify it below.',
    'html_project_project_success' => 'Project has been assigned to this installation.',
    'html_project_project_project_id' => 'Project ID',
    'html_project_project_check' => 'Check',
    'html_project_project_remove' => 'Remove',
    'html_project_project_find' => 'How to find your Project ID',
    'html_project_project_name' => 'Name',
    'html_project_project_owner' => 'Owner',
    'html_project_project_description' => 'Description',
    'html_project_project_view_plugins' => 'View Plugins',
    'html_project_project_view_themes' => 'View Themes',

    /* project/plugins.htm */
    'html_project_plugins_lead' => 'Choose some plugins to get you started (optional).',
    'html_project_plugins_included' => 'Included',
    'html_project_plugins_no_included' => 'There are no plugins included.',
    'html_project_plugins_recommended' => 'Recommended',
    'html_project_plugins_included_project' => 'These plugins are included with the project',

    /* project/plugin.htm */

    /* project/fail.htm */
    'html_project_fail_h4' => 'Check the supplied Project ID',

    /* progress/fail.htm */
    'html_progress_fail_h4' => 'Progress failed',
    'html_progress_fail_p' => 'Something went wrong during the installation. Please check the log file or see <a href="http://octobercms.com/docs/help/install#troubleshoot-installation" target="_blank">the documentation</a> for more information.',
    'html_progress_fail_try' => 'Try again',

    /* config/sqlsrv.htm */
    'html_config_sqlsrv_host' => 'SQL Host',
    'html_config_sqlsrv_host_help' => 'Specify the hostname for the database connection.',
    'html_config_sqlsrv_port' => 'SQL Port',
    'html_config_sqlsrv_port_help' => '(Optional) Specify a non-default port for the database connection.',
    'html_config_sqlsrv_name' => 'Database Name',
    'html_config_sqlsrv_name_help' => 'Specify the name of the empty database.',
    'html_config_sqlsrv_user' => 'SQL Login',
    'html_config_sqlsrv_user_help' => 'User with all privileges in the database.',
    'html_config_sqlsrv_pass' => 'SQL Password',
    'html_config_sqlsrv_pass_help' => 'Password for the specified user.',

    /* config/sqlite.htm */
    'html_config_sqlite_name' => 'SQLite Database Path',
    'html_config_sqlite_name_help' => 'Specify a relative or absolute path to the SQLite database file. Path is relative to the application root directory.',

    /* config/pgsql.htm */
    'html_config_pgsql_host' => 'Postgres Host',
    'html_config_pgsql_host_help' => 'Specify the hostname for the database connection.',
    'html_config_pgsql_port' => 'Postgres Port',
    'html_config_pgsql_port_help' => '(Optional) Specify a non-default port for the database connection.',
    'html_config_pgsql_name' => 'Database Name',
    'html_config_pgsql_name_help' => 'Specify the name of the empty database.',
    'html_config_pgsql_user' => 'Postgres Login',
    'html_config_pgsql_user_help' => 'User with all privileges in the database.',
    'html_config_pgsql_pass' => 'Postgres Password',
    'html_config_pgsql_pass_help' => 'Password for the specified user.',

    /* config/mysql.htm */
    'html_config_mysql_host' => 'MySQL Host',
    'html_config_mysql_host_help' => 'Specify the hostname for the database connection.',
    'html_config_mysql_port' => 'MySQL Port',
    'html_config_mysql_port_help' => '(Optional) Specify a non-default port for the database connection.',
    'html_config_mysql_name' => 'Database Name',
    'html_config_mysql_name_help' => 'Specify the name of the empty database.',
    'html_config_mysql_user' => 'MySQL Login',
    'html_config_mysql_user_help' => 'User with all privileges in the database.',
    'html_config_mysql_pass' => 'MySQL Password',
    'html_config_mysql_pass_help' => 'Password for the specified user.',

    /* config/fail.htm */
    'html_config_fail_p' => 'There is a problem with the specified {{label}} configuration.',

	/* config/database.htm */
	'html_config_database_lead' => 'Please prepare an empty database for this installation.',
	'html_config_database_type' => 'Database Type',
	'html_config_database_help' => 'Please specify the database driver type for this connection.',

	/* config/advanced.htm */
	'html_config_advanced_lead' => 'Provide a custom URL for the Administration Area.',
	'html_config_advanced_backend' => 'Backend URL',
	'html_config_advanced_backend_help' => 'Please specify a value which you will use to access the Backend.',
	'html_config_advanced_lead2' => 'Specify a unique code for protecting sensitive data, such as user passwords.',
	'html_config_advanced_encryption' => 'Encryption Code',
	'html_config_advanced_encryption_help' => 'The encryption code should be of a valid length (16, 24, 32).',
	'html_config_advanced_lead3' => 'Specify a permission mask for folders and files used for installation and software updates.',
	'html_config_advanced_permission_file' => 'File Permission Mask',
	'html_config_advanced_permission_folder' => 'Folder Permission Mask',

	/* config/admin.htm */
	'html_config_admin_lead' => 'Please specify details for logging in to the Administration Area.',
	'html_config_admin_first' => 'First Name',
	'html_config_admin_last' => 'Last Name',
	'html_config_admin_email' => 'Email Address',
	'html_config_admin_login' => 'Admin Login',
	'html_config_admin_password' => 'Admin Password',
	'html_config_admin_password_confirm' => 'Confirm Password',

	/* check/fail.htm */
	'html_check_fail_h4' => 'System Check Failed',
	'html_check_fail_p' => '{{#reason}}{{reason}}{{/reason}} {{^reason}}Your system does not meet the minimum requirements for the installation.{{/reason}}Please see <a href="http://octobercms.com/docs/help/installation" target="_blank">the documentation</a> for more information.',
	'html_check_fail_p2' => 'Retry System Check',
	'html_check_fail_small' => 'Reason code: ',
];