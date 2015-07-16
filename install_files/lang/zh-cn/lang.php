<?php
/*
 * English language file
 */

return [
    /* InstallerException.php */

    /* InstallerRewrite.php */
    'installer_rewrite_key_not_exist' => '配置文件中无法找到设置项 "%s",重写失败！',
    'installer_rewrite_failed' => '重写设置项 "%s" 失败',

    /* Installer.php */
    'installer_invalid_handler' => '无效的handler: %s',
    'installer_specify_database_host' => '请输入数据库地址',
    'installer_specify_database_name' => '请输入数据库名称',
    'installer_connection_failed' => '连接失败: ',
    'installer_database_not_empty' => '数据库 "%s" 不为空，请清空数据库或者输入其它的空数据库。',
    'installer_specify_admin_first_name' => '请输入管理员的名字',
    'installer_specify_admin_last_name' => '请输入管理员的姓氏',
    'installer_specify_admin_email' => '请输入管理员的电子邮箱地址',
    'installer_specify_valid_email' => '请输入一个有效的电子邮箱地址',
    'installer_specify_admin_password' => '请输入密码',
    'installer_confirm_admin_password' => '请再次输入密码',
    'installer_specify_password_not_match_confirm' => '两次密码输入不匹配',
    'installer_specify_encryption_key' => '请输入加密密钥',
    'installer_specify_valid_key' => '加密密钥的长度必须是(%s).',
    'installer_specify_folder_permission_mask' => '请输入文件夹权限掩码',
    'installer_specify_file_permission_mask' => '请输入文件权限掩码',
    'installer_specify_folder_valid_mask' => '请输入有效的文件夹权限掩码',
    'installer_specify_file_valid_mask' => '请输入有效的文件权限掩码',
    'installer_plugin_download_failed' => '插件下载失败，找不到该名称',
    'installer_theme_download_failed' => '主题下载失败，找不到该名称',
    'installer_unable_open_core' => '无法打开程序压缩文件',
    'installer_unable_extract_core' => '无法解压程序文件',
    'installer_unable_open_plugin' => '无法打开插件压缩文件',
    'installer_unable_open_theme' => '无法打开主题压缩文件',
    'installer_unable_find_autoloader' => '无法找到自动加载器: ~/bootstrap/autoload.php',
    'installer_unable_find_app_loader' => '无法找到程序加载器: ~/bootstrap/app.php',
    'installer_server_responded_error' => '服务器返回错误：',
    'installer_server_no_response' => '服务器无响应。',
    'installer_server_invalid_response' => '服务器返回一个错误的响应。',
    'installer_failed_create_temporary_directory' => '无法在 %s 中创建临时文件夹，请检查这个目录是否有写入权限。',
    'installer_failed_deliver_package' => '服务器无法传输这个包',
    'installer_corrupt_package' => '服务器下载的包文件错误',

    /* install.php */
    'install_title' => 'October 安装程序',
    'install_system_check_title' => '系统检查',
    'install_system_check_next' => '同意并继续',
    'install_system_check_php_version' => '需要 PHP 5.4 或以上版本',
    'install_system_check_required_curl' => '需要 cURL 扩展',
    'install_system_check_test_connection' => '测试连接到安装服务器',
    'install_system_check_write_permission' => '文件夹及文件写入权限',
    'install_system_check_write_permission_reason' => '安装程序无法写入安装目录及文件。',
    'install_system_check_required_pdo' => '需要 PDO 扩展',
    'install_system_check_required_mcrypt' => '需要 MCrypt 扩展',
    'install_system_check_required_mbstring' => '需要 Mbstring 扩展',
    'install_system_check_required_openssl' => '需要 OpenSSL 扩展',
    'install_system_check_required_zip' => '需要 ZipArchive 扩展',
    'install_system_check_required_gd' => '需要 GD 扩展',
    'install_config_form_title' => '设置',
    'install_config_form_next' => '继续',
    'install_config_form_database_label' => '数据库',
    'install_config_form_database_category' => '常规',
    'install_config_form_admin_label' => '管理员',
    'install_config_form_admin_category' => '常规',
    'install_config_form_advanced_label' => '高级',
    'install_config_form_advanced_category' => '高级',
    'install_starter_form_title' => '开始使用',
    'install_themes_form_title' => '从主题开始',
    'install_project_form_title' => '项目详情',
    'install_project_form_next' => '安装！',
    'install_project_form_project' => '项目',
    'install_project_form_plugins' => '插件',
    'install_project_form_themes' => '主题',
    'install_install_progress_title' => '安装中...',
    'install_install_progress_get_meta_data' => '获取包信息',
    'install_install_progress_download_core' => '下载程序文件',
    'install_install_progress_download_plugins' => '下载插件：',
    'install_install_progress_download_themes' => '下载主题：',
    'install_install_progress_extract_core' => '解压程序文件',
    'install_install_progress_extract_plugins' => '解压插件：',
    'install_install_progress_extract_themes' => '解压主题：',
    'install_install_progress_setup_config' => '创建配置文件',
    'install_install_progress_create_admin' => '创建管理员账号',
    'install_install_progress_setup_project' => '设置网站项目',
    'install_install_progress_finish_install' => '完成安装',
    'install_install_complete_title' => '恭喜！',

    /* title.htm */

    /* themes.htm */
    'html_themes_lead' => '加载主题...',
    'html_themes_text_center' => '主题将会自动安装需要的插件并创建您的网站所需要的页面。',

    /* starter.htm */
    'html_starter_text_center' => '您希望如何设置你的站点？',
    'html_starter_start_scratch' => '从零开始',
    'html_starter_scratch_p1' => '不安装任何插件或者主题，这很适合完全自助建站。',
    'html_starter_scratch_p2' => '你需要使用HTML/CSS来编写您的站点。',
    'html_starter_start_theme' => '从主题开始',
    'html_starter_theme_p1' => '在一个通用主题的基础上建站。',
    'html_starter_theme_p2' => '示例：一个博客站点，一个作品集站点。',
    'html_starter_start_project' => '使用一个项目ID',
    'html_starter_project_p1' => '如果您已经在OctoberCMS网站上建立了一个项目，您可以在这里输入它。',
    'html_starter_project_p2' => '这个选项通常用于手动选择插件和主题。',

    /* project.htm */
    'html_project_h3' => '自定义安装',
    'html_project_p1' => '不同于提供一个项目ID，您可以手动定义插件和主题。',
    'html_project_p2' => '完成后，点击安装继续。',

    /* progress.htm */

    /* header.htm */
    'html_header_h1' => 'October',

    /* footer.htm */

    /* config.htm */

    /* complete.htm */
    'html_complete_lead' => '安装程序已经成功完成全部安装',
    'html_complete_app_h4' => '网站地址',
    'html_complete_app_p' => '您的网站地址：',
    'html_complete_app_h4_2' => '管理后台',
    'html_complete_app_p_2' => '使用下面的链接进入管理后台：',
    'html_complete_post_h4' => '安装后配置',
    'html_complete_post_p' => '现在安装已经完成，这里有些您可能感兴趣的能让您更好的操作您的网站的资料。请查看<a href="http://octobercms.com/docs/help/installation#post-install-config" target="_blank">安装帮助指南</a>获取更多用法。',
    'html_complete_clean_h4' => '重要！',
    'html_complete_clean_p' => '出于安全考虑，您需要删除安装文件：<strong>install.php</strong>脚本以及<strong>install_files</strong>目录。',

    /* check.htm */
    'html_check_language' => '语言：',
    'html_check_h3' => '许可协议',
    'html_check_p1' => 'MIT license',
    'html_check_p2' => 'Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:',
    'html_check_p3' => 'The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.',
    'html_check_p4' => 'THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.',

    /* themes/theme.htm */
    'html_themes_theme_span' => '安装这个？',
    'html_themes_theme_confirm' => '确定',
    'html_themes_theme_cancel' => '取消',
    'html_themes_theme_demo' => '演示',
    'html_themes_theme_details' => '详情',
    'html_themes_theme_install' => '安装',

    /* project/themes.htm */
    'html_project_themes_lead' => '选择您想安装的主题（可选）。',
    'html_project_themes_search' => '搜索主题...',
    'html_project_themes_included' => '已选择',
    'html_project_themes_no_included' => '没有选择主题。',
    'html_project_themes_recommended' => '推荐',
    'html_project_themes_included_project' => '这些主题中包含项目',

    /* project/theme.htm */

    /* project/suggestion.htm */

    /* project/project.htm */
    'html_project_project_lead' => '如果您希望本次安装使用某个项目，请在下面选择它。',
    'html_project_project_success' => '项目已关联到本次安装。',
    'html_project_project_project_id' => '项目ID',
    'html_project_project_check' => '检查',
    'html_project_project_remove' => '删除',
    'html_project_project_find' => '如何查找您的项目ID',
    'html_project_project_name' => '名称',
    'html_project_project_owner' => '拥有着',
    'html_project_project_description' => '描述',
    'html_project_project_view_plugins' => '查看插件',
    'html_project_project_view_themes' => '查看主题',

    /* project/plugins.htm */
    'html_project_plugins_lead' => '选择您想安装的插件（可选）',
    'html_project_plugins_search' => '搜索插件...',
    'html_project_plugins_included' => '已选择',
    'html_project_plugins_no_included' => '没有选择插件。',
    'html_project_plugins_recommended' => '推荐',
    'html_project_plugins_included_project' => '这些插件中包含项目',

    /* project/plugin.htm */

    /* project/fail.htm */
    'html_project_fail_h4' => '检查提供的项目ID',

    /* progress/fail.htm */
    'html_progress_fail_h4' => '安装失败',
    'html_progress_fail_p' => '安装过程出现了错误。请检查安装日志或者查看<a href="http://octobercms.com/docs/help/install#troubleshoot-installation" target="_blank">文档</a>获取更多信息。',
    'html_progress_fail_try' => '重试',

    /* config/sqlsrv.htm */
    'html_config_sqlsrv_host' => 'SQL 地址',
    'html_config_sqlsrv_host_help' => '输入数据库连接地址。',
    'html_config_sqlsrv_port' => 'SQL 端口',
    'html_config_sqlsrv_port_help' => '（可选）输入一个非默认的数据库连接端口。',
    'html_config_sqlsrv_name' => '数据库名',
    'html_config_sqlsrv_name_help' => '输入一个空的数据库名称。',
    'html_config_sqlsrv_user' => 'SQL 用户名',
    'html_config_sqlsrv_user_help' => '输入拥有这个数据库完整权限的用户名。',
    'html_config_sqlsrv_pass' => 'SQL 密码',
    'html_config_sqlsrv_pass_help' => '输入这个用户的密码。',

    /* config/sqlite.htm */
    'html_config_sqlite_name' => 'SQLite 数据库地址',
    'html_config_sqlite_name_help' => '输入SQLite数据库文件的相对或者绝对路径；相对路径是相对于程序根目录。',

    /* config/pgsql.htm */
    'html_config_pgsql_host' => 'Postgres 地址',
    'html_config_pgsql_host_help' => '输入数据库连接地址。',
    'html_config_pgsql_port' => 'Postgres 端口',
    'html_config_pgsql_port_help' => '（可选）输入一个非默认的数据库连接端口。',
    'html_config_pgsql_name' => '数据库名',
    'html_config_pgsql_name_help' => '输入一个空的数据库名称。',
    'html_config_pgsql_user' => 'Postgres 用户名',
    'html_config_pgsql_user_help' => '输入拥有这个数据库完整权限的用户名。',
    'html_config_pgsql_pass' => 'Postgres 密码',
    'html_config_pgsql_pass_help' => '输入这个用户的密码。',

    /* config/mysql.htm */
    'html_config_mysql_host' => 'MySQL 地址',
    'html_config_mysql_host_help' => '输入数据库连接地址。',
    'html_config_mysql_port' => 'MySQL 端口',
    'html_config_mysql_port_help' => '（可选）输入一个非默认的数据库连接端口。',
    'html_config_mysql_name' => '数据库名',
    'html_config_mysql_name_help' => '输入一个空的数据库名称。',
    'html_config_mysql_user' => 'MySQL 用户名',
    'html_config_mysql_user_help' => '输入拥有这个数据库完整权限的用户名。',
    'html_config_mysql_pass' => 'MySQL 密码',
    'html_config_mysql_pass_help' => '输入这个用户的密码。',

    /* config/fail.htm */
    'html_config_fail_p' => '在设置{{label}}时遇到了一个问题',

	/* config/database.htm */
	'html_config_database_lead' => '请为安装程序准备一个空的数据库。',
	'html_config_database_type' => '数据库类型',
	'html_config_database_help' => '请输入这个连接的数据库驱动类型。',

	/* config/advanced.htm */
	'html_config_advanced_lead' => '提供一个管理后台的自定义路径。',
	'html_config_advanced_backend' => '后台地址',
	'html_config_advanced_backend_help' => '请输入您想要访问后台的地址。',
	'html_config_advanced_lead2' => '请输入一个独特的代码来保护敏感数据，比如用户密码。',
	'html_config_advanced_encryption' => '加密代码',
	'html_config_advanced_encryption_help' => '这个加密代码长度必须是(16, 24, 32)。',
	'html_config_advanced_lead3' => '请输入安装程序及软件更新程序的目录和文件的权限掩码。',
	'html_config_advanced_permission_file' => '文件权限掩码',
	'html_config_advanced_permission_folder' => '文件夹权限掩码',

	/* config/admin.htm */
	'html_config_admin_lead' => '请输入管理员详细信息。',
	'html_config_admin_first' => '名字',
	'html_config_admin_last' => '姓氏',
	'html_config_admin_email' => '电子邮件',
	'html_config_admin_login' => '登录用户名',
	'html_config_admin_password' => '密码',
	'html_config_admin_password_confirm' => '确认密码',

	/* check/fail.htm */
	'html_check_fail_h4' => '系统检查失败',
	'html_check_fail_p' => '{{#reason}}{{reason}}{{/reason}} {{^reason}}您的系统不满足安装程序的最低要求。{{/reason}}请参考<a href="http://octobercms.com/docs/help/installation" target="_blank">文档</a>获取更多信息。',
	'html_check_fail_p2' => '重试系统检查',
	'html_check_fail_small' => '返回代码：',
];