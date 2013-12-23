<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width">
        <title>October Installation</title>
        <link href="install_files/style.css" rel="stylesheet">
        <script src="install_files/script.js"></script>
    </head>
    <body class="js">
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

        <section class="title">
            <div class="container">

                <div class="row">
                    <div class="col-md-7">
                        <h2 class="animate move_right">Configuration</h2>
                    </div>
                    <div class="col-md-5 visible-md visible-lg">

                        <div class="steps row animate move_up">
                            <div class="col-sm-4"><p class="pass">1</p></div>
                            <div class="col-sm-4"><p class="pass">2</p></div>
                            <div class="col-sm-4"><p>3</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="body">
            <div class="container">

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

                <a href="#" class="btn btn-default btn-lg btn-block">Advanced Options</a>

            </div>
        </section>


        <footer>
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-inline">
                            <li><a target="_blank" href="http://octobercms.com/">October</a></li>
                            <li><a target="_blank" href="http://octobercms.com/docs">Documentation</a></li>
                            <li><a target="_blank" href="http://octobercms.com/about">About</a></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <a href="" class="btn btn-primary btn-lg pull-right">Continue</a>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>