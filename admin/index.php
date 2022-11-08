<?php

require_once 'authentication.php';

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../favicon.ico">

    <title>Attendance Monitoring System | Profiles</title>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="../css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="jumbotron-narrow.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="../js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	<style type="text/css">
	
		body {
			padding-top: 0!important;
			background-color: #F8F8F8;
		}
		
		#content {
			background-color: #fff;
			border: 1px solid #e0e0e0;
			border-radius: 3px;
			padding-top: 25px;
			padding-bottom: 25px;
		}
		
		#crud-buttons {
			border-bottom: 1px solid #e5e5e5;
			padding: 0 15px 15px;
		}		
		
		#page-content {
			margin-top: 25px;
		}
		
		input[type="checkbox"] {
			vertical-align: middle;
		}
		
		#frmProfile {
			margin-left: 20px;
		}
		
		#frmProfile img {
			width: 50%;
			margin-bottom: 10px;
		}
		
		#proPic {
			margin-bottom: 5px;
		}
	
	</style>
  </head>

  <body ng-app="appProfile"  ng-controller="appProfileCtrl">

	<nav class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">LZDS</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
			<li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="javascript:;" logout-account>Logout</a></li>
                <!--<li role="separator" class="divider"></li>-->
              </ul>
            </li>			
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
  
    <div class="container">
      <div class="header clearfix">
        <nav>
          <ul class="nav nav-pills pull-right">
            <li role="presentation" class="active"><a href="index.php">Profiles</a></li>
            <li role="presentation"><a href="dtr.php">DTR</a></li>
            <li role="presentation"><a href="console.php">Console</a></li>
            <li role="presentation"><a href="preferences.php">Preferences</a></li>
            <li role="presentation"><a href="about.php">About</a></li>			
          </ul>
        </nav>
        <!--<h3 class="text-muted">LZDS</h3>-->
      </div>

		<div id="content" class="row">
			<div class="col-lg-12">
				<form id="crud-buttons" class="form-inline">
					<div class="form-group">
						<button type="button" class="btn btn-primary" ng-disabled="addBtn" ng-click="loadFormProfile()">New</button>
						<button type="button" class="btn btn-primary" ng-disabled="editBtn" ng-click="editProfile()">Edit</button>
						<button type="button" class="btn btn-primary" ng-disabled="delBtn" ng-click="confirmDelProfile()">Delete</button>
						<label>School Year:</label>
						<select class="form-control" ng-model="filter.profile_type" ng-options="pt.description for pt in view.profileTypes track by pt.name"></select>
						<select class="form-control" ng-model="filter.sy" ng-options="sy.school_year for sy in school_years track by sy.id" ng-show="filter.profile_type.name == 'Student'"></select>
						<button type="button" class="btn btn-primary" ng-click="profileList()">Go!</button>
						<input type="text" class="form-control" ng-disabled="searchIn" ng-model="q" placeholder="Search">						
					</div>
				</form>
			</div>
			<div id="page-content" class="col-lg-12" ng-include="activeTemplate"></div>			
		</div>
      <footer class="footer">
        <p>&copy; 2016 AutoPilot</p>
      </footer>

    </div> <!-- /container -->

	<div id="confirm" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="label-confirm">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="label-confirm">Modal title</h4>
		  </div>
		  <div class="modal-body">
			<p>One fine body&hellip;</p>
		  </div>
		  <div class="modal-footer">
		  </div>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div id="notify" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="label-notify">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="label-notify">Modal title</h4>
		  </div>
		  <div class="modal-body">
			<p>One fine body&hellip;</p>
		  </div>
		  <div class="modal-footer">
		  </div>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	
    <script src="angularjs/angular.min.js"></script>	
    <script src="../jquery/jquery-2.2.4.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>	
	
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="../js/ie10-viewport-bug-workaround.js"></script>
	
    <script src="../jquery/jquery.blockUI.js"></script>	
	
	<script src="angularjs/utils/pagination/dirPagination.js"></script>
	<script src="angularjs/utils/checklist-model.js"></script>
	<script src="angularjs/utils/ui-bootstrap-tpls-1.3.3.min.js"></script>
	<script src="modules/bootstrap-modal.js"></script>
	<script src="modules/account.js"></script>
	<script src="modules/school-year.js"></script>
	
	<script src="controllers/appProfile.js"></script>	
  </body>
</html>