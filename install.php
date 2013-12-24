<?php include 'install_files/class.php'; ?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width">
        <title>October Installation</title>
        <link href="install_files/style.css" rel="stylesheet">
        <script src="install_files/script.js"></script>
    </head>
    <body class="js">

        <!-- Header -->
        <header>
            <div class="container">
                <div class="row">
                    <div class="col-md-7">
                        <h1>October</h1>
                    </div>
                    <div class="col-md-5">

                        <div class="progress animate little_bounce" style="display:none">
                            <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
                                <span class="sr-only">60% Complete</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </header>

        <!-- Title -->
        <section class="title">
            <div class="container" id="containerTitle"></div>
        </section>

        <!-- Body -->
        <section class="body">
            <div class="container" id="containerBody"></div>
        </section>

        <!-- Footer -->
        <footer>
            <div class="container" id="containerFooter"></div>
        </footer>

        <!-- Common -->
        <script type="text/template" id="partialTitle">
            <div class="row">
                <div class="col-md-7">
                    <h2 class="animate move_right">{{title}}</h2>
                </div>
                <div class="col-md-5 visible-md visible-lg">

                    <div class="steps row {{#isStep1}}animate move_up{{/isStep1}}">
                        <div class="col-sm-4 {{#isStep3}}pass{{/isStep3}}{{#isStep2}}pass{{/isStep2}}{{#isStep1}}pass last{{/isStep1}}"><p>1</p></div>
                        <div class="col-sm-4 {{#isStep3}}pass{{/isStep3}}{{#isStep2}}pass last{{/isStep2}}"><p>2</p></div>
                        <div class="col-sm-4 {{#isStep3}}pass last{{/isStep3}}"><p>3</p></div>
                    </div>
                </div>
            </div>
        </script>

        <script type="text/template" id="partialFooter">
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-inline">
                        <li><a target="_blank" href="http://octobercms.com/">October</a></li>
                        <li><a target="_blank" href="http://octobercms.com/docs">Documentation</a></li>
                        <li><a target="_blank" href="http://octobercms.com/about">About</a></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <a href="javascript:Installer.Events.next()" class="btn btn-primary btn-lg pull-right" id="nextButton">{{nextButton}}</a>
                </div>
            </div>
        </script>

        <!-- Checker Page -->
        <script>
            Installer.Pages.systemCheck.title = 'System Check'
            Installer.Pages.systemCheck.nextButton = 'Agree & Continue'

            Installer.Pages.systemCheck.requirements = [
                { code: 'liveConnection', label: 'Test connection to the installation server' },
                { code: 'writePermission', label: 'Permission to write to the installation directory' },
                { code: 'phpVersion', label: 'PHP version 5.4 or greater installed' },
                { code: 'safeMode', label: 'Safe mode PHP setting is not enabled' },
                { code: 'curlLibrary', label: 'cURL PHP library is installed' },
                { code: 'mcryptLibrary', label: 'Mcrypt PHP library is installed' }
            ]
        </script>
        
        <script type="text/template" id="partialSystemCheck">
            <ul class="list-unstyled system-check" id="systemCheckList"></ul>

            <div id="systemCheckFailed" class="system-check-failed animated-content"></div>

            <div id="appEula" class="app-eula animated-content">
                <h3>EULA <small>End-user license agreement</small></h3>
                <div class="scroll-panel">
                    MIT license <br />
                    Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:<br />
                    <br />
                    The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.<br />
                    <br />
                    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.<br />
                    <br />
                </div>
            </div>
        </script>

        <script type="text/template" id="partialSystemCheckFailed">
            <div class="callout callout-danger">
            <h4>System Check Failed!</h4>
            <p>Your system does not meet the minimum requirements for the installation. Please see <a href="http://octobercms.com/docs" target="_blank">the documentation</a> for more information.</p>
            <p><a href="javascript:Installer.Events.retry()" class="btn btn-default btn-sm">Retry System Check</a></p>
            <small class="text-muted">Reason code: {{reason}}</small>
            </div>
        </script>

        <!-- Config Page -->
        <script>
            Installer.Pages.configForm.title = 'Configuration'
            Installer.Pages.configForm.nextButton = 'Continue'
        </script>

        <script type="text/template" id="partialConfigForm">
            <div id="configForm" class="animated-content">
                <div class="row">
                    <div class="col-md-6">

                        <h3>Database</h3>
                        <div class="form-group">
                            <label for="db_type">Database Type</label>
                            <select name="db_type" class="form-control input-lg">
                                <option>MySQL</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="db_host">MySQL Host</label>
                            <input name="db_host" class="form-control input-lg" value="localhost">
                        </div>

                        <div class="form-group">
                            <label for="db_name">Database Name</label>
                            <input name="db_name" class="form-control input-lg" value="" placeholder="Name of empty database">
                        </div>

                        <div class="form-group">
                            <label for="db_user">MySQL User</label>
                            <input name="db_user" class="form-control input-lg" value="" placeholder="User with all privileges in the database">
                        </div>

                        <div class="form-group">
                            <label for="db_password">MySQL Password</label>
                            <input name="db_password" class="form-control input-lg" value="">
                        </div>

                    </div>
                    <div class="col-md-6">

                        <h3>Administrator</h3>
                        <div class="form-group">
                            <label for="admin_login">Admin Login</label>
                            <input name="admin_login" class="form-control input-lg" value="admin">
                        </div>

                        <div class="form-group">
                            <label for="admin_password">Admin Password</label>
                            <input name="admin_password" class="form-control input-lg" value="admin">
                        </div>

                        <div class="form-group">
                            <label for="admin_email">Email</label>
                            <input name="admin_email" class="form-control input-lg" value="admin@admin.admin">
                        </div>

                        <div class="form-group">
                            <label for="admin_first_name">First Name</label>
                            <input name="admin_first_name" class="form-control input-lg" value="Adam">
                        </div>


                        <div class="form-group">
                            <label for="admin_last_name">Surname</label>
                            <input name="admin_last_name" class="form-control input-lg" value="Person">
                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <a href="javascript:Installer.Pages.configForm.showAdvanced()" class="btn btn-default btn-lg btn-block" id="configFormShowAdvanced">Show Advanced Options</a>
                </div>

                <div class="advanced-options" id="advancedOptions">
                    <h3>Advanced</h3>

                    <div class="row">
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="folder_mask">File Permission Mask</label>
                                <input name="folder_mask" class="form-control input-lg" value="777">
                            </div>

                            <div class="form-group">
                                <label for="file_mask">Folder Permission Mask</label>
                                <input name="file_mask" class="form-control input-lg" value="777">
                            </div>

                        </div>
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="backend_url">Administration URL</label>
                                <input name="backend_url" class="form-control input-lg" value="/backend">
                            </div>

                            <div class="form-group">
                                <label for="encryption_code">Encryption Code</label>
                                <input name="encryption_code" class="form-control input-lg" value="RANDOM_STRING">
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </script>

        <!-- Packages Page -->
        <script>
            Installer.Pages.packageInstall.title = 'Packages'
            Installer.Pages.packageInstall.nextButton = 'Install!'

            Installer.Pages.packageInstall.includedPlugins = [
                { name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                { name: 'Blog', author: 'October', description: 'A robust blogging platform.', image: 'http://placehold.it/100x100' }
            ]
            
            Installer.Pages.packageInstall.suggestedPlugins = [
                { name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                { name: 'Blog', author: 'October', description: 'A robust blogging platform.', image: 'http://placehold.it/100x100' },
                { name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                { name: 'Blog', author: 'October', description: 'A robust blogging platform.', image: 'http://placehold.it/100x100' },
                { name: 'Demo', author: 'October', description: 'Demonstration features.', image: 'http://placehold.it/100x100' },
                { name: 'Blog', author: 'October', description: 'A robust blogging platform.', image: 'http://placehold.it/100x100' }
            ]
        </script>

        <script type="text/template" id="partialPackageInstall">
            <div class="package-search">
                <input class="typeahead" placeholder="search packages to install..." id="packageSearchInput">
            </div>

            <h3>Plugins to install</h3>
            <p>These plugins below will be included with your installation.</p>
            <ul class="plugin-list" id="pluginList"></ul>

            <h3>Recommended Plugins</h3>
            <div class="scroll-panel">
                <div class="row suggested-plugins" id="suggestedPlugins"></div>
            </div>
        </script>

        <script type="text/template" id="partialPackageInstallSuggestion">
            <div class="col-md-6 plugin">
                <div class="image"><img src="{{image}}" alt=""></div>
                <div class="details">
                    <h5>{{author}}.{{name}}</h5>
                    <p>{{description}}</p>
                </div>
            </div>
        </script>

        <script type="text/template" id="partialPackageInstallPlugin">
            <li>
                <h4>{{name}}</h4>
                <img src="{{image}}" alt="">
                <p>by {{author}}</p>
                <button type="button" class="close" aria-hidden="true">&times;</button>
            </li>
        </script>
    </body>
</html>