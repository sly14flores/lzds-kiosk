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

    <title>Attendance Monitoring System | DTR</title>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

	<link rel="stylesheet prefetch" href="font-awesome-4.7.0/css/font-awesome.min.css">	
	
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="../css/ie10-viewport-bug-workaround.css" rel="stylesheet">
	
    <!-- Custom styles for this template -->
    <link href="jumbotron-narrow.css" rel="stylesheet">
	
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/left-side.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="../js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="jspdf/pure-min.css">
    <link rel="stylesheet" href="jspdf/grids-responsive-min.css">	
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
			overflow: visible;
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
		
		#tasks {
			text-align: right;
			padding-right: 10px;
			padding-bottom: 5px;
		}
		
		#tasks span:hover {
			cursor: pointer;
		}
		
		.staff-logs p {
			margin-bottom: 0!important;
		}
		
		.staff-logs table {
			margin-top: 15px!important;
			margin-bottom: 15px!important;
		}
		
		.staff-logs table tbody tr {
			cursor: move;
		}
		
		.staff-logs table tbody tr td:last-child i {
			cursor: pointer;		
		}

	</style>
  </head>

  <body ng-app="appDTR"  ng-controller="appDTRCtrl">

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
            <li role="presentation"><a href="index.php">Profiles</a></li>
            <li role="presentation" class="active"><a href="dtr.php">DTR</a></li>
            <li role="presentation"><a href="console.php">Console</a></li>
            <li role="presentation"><a href="preferences.php">Preferences</a></li>			
            <li role="presentation"><a href="about.php">About</a></li>
          </ul>
        </nav>
        <!--<h3 class="text-muted">LZDS</h3>-->
      </div>

		<div id="content" class="row">
			<div class="col-lg-12">
				<form id="crud-buttons">
					<div class="row">
					<div class="col-lg-4">
						<div class="form-group">
							<label class="control-label">Profile Type</label>
							<select name="select-profile-type" class="form-control" ng-model="views.profileType" ng-options="x for (x,y) in views.profileTypes track by y" profile-type-select>
							  <option value="">-</option>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Full Name</label>
							<input type="text" class="form-control" placeholder="Full Name" ng-model="filter.fullname" uib-typeahead="fullname as rfid.fullname for rfid in views.rfids | filter:{fullname:$viewValue}" typeahead-on-select="fullnameSelected($item, $model, $label, $event)">
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label class="control-label">Filter By</label>
							<select name="select-filter-by" class="form-control" ng-model="views.filterBy" ng-options="x for (x,y) in views.filterBys track by y" filter-by-select ng-disabled="views.filterByDisabled">
							  <option value="">-</option>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Period</label>
							<select name="select-period" class="form-control" ng-model="filter.period" ng-options="x for (x,y) in views.periods track by y" ng-disabled="views.periodDisabled">
                <option value="">-</option>
							</select>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label class="control-label">Month</label>
							<select name="select-month" class="form-control" ng-model="filter.month" ng-options="x for (x,y) in views.months track by y" ng-disabled="views.monthDisabled"><option value="">-</option></select>
						</div>					
						<div class="form-group">
							<label class="control-label">Specific Date</label>
							<p class="input-group">
							  <input type="text" class="form-control" uib-datepicker-popup="{{views.format}}" ng-model="filter.dateSpecific" is-open="views.popupDate.opened" datepicker-options="dateOptions" close-text="Close" alt-input-formats="altInputFormats" ng-disabled="views.dateSpecificDisabled">
							  <span class="input-group-btn">
								<button type="button" class="btn btn-default" ng-click="dateOpen()" ng-disabled="views.dateSpecificDisabled"><i class="glyphicon glyphicon-calendar"></i></button>
							  </span>
							</p>
						</div>
					</div>
					</div>
					<div class="row">
						<div class="col-lg-4">&nbsp;</div>
						<div class="col-lg-4">&nbsp;</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label class="control-label">Year</label>
								<input type="text" class="form-control" placeholder="Year" ng-model="filter.year">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-4">&nbsp;</div>
						<div class="col-lg-4">&nbsp;</div>
						<div class="col-lg-4"><button type="submit" class="btn btn-primary pull-right" ng-click="fetchDTR()">Go!</button></div>
					</div>
				</form>
			</div>
			<div id="page-content" class="col-lg-12" ng-include="activeTemplate"></div>
		</div>
      <footer class="footer">
        <p>&copy; 2016 AutoPilot</p>
      </footer>

    </div> <!-- /container -->

	<!-- left side menu -->
	<div class="ace-settings-container">
		<div class="btn btn-app btn-md btn-primary ace-settings-btn" ng-class="{'open': views.togPrint}" ng-click="views.togPrint = !views.togPrint">
			<i ng-class="{'fa': true, 'fa-print': !views.togPrint, 'fa-times': views.togPrint, 'bigger-130': true}"></i>
		</div>

		<div class="ace-settings-box clearfix" ng-class="{'open': views.togPrint}">
			<div class="dtr-report">
				<form>
					<div class="form-group">
						<label class="control-label">Profile Type</label>
						<select class="form-control" ng-model="report.profileType" ng-options="x for (x,y) in views.profileTypes track by y" ng-change="selectReport(report.profileType)">
						  <option value="">-</option>
						</select>
					</div>
					<div ng-include="views.reportFilter"></div>
				</form>
			</div>
		</div>
	</div>	
	
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

	<div id="modal-show" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="label-modal-show">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="label-modal-show">Modal title</h4>
		  </div>
		  <div class="modal-body">
			<p>One fine body&hellip;</p>
		  </div>
		  <div class="modal-footer">
		  </div>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	
	<form id="form2excel" method="post" action="reports/form2excel.php" target="_blank">
		<input type="hidden" name="form2excel" value="{{filterStr}}">
	</form>
	
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="../js/ie10-viewport-bug-workaround.js"></script>

    <script src="angularjs/angular.min.js"></script>
    <script src="../jquery/jquery-2.2.4.min.js"></script>
    <script src="../jquery/jquery.blockUI.js"></script>	
    <script src="../js/bootstrap.min.js"></script>		
    <script src="../bootstrap-notify-3.1.3/bootstrap-notify.min.js"></script>
	<script src="controllers/appDTR.js"></script>
	<script src="angularjs/utils/pagination/dirPagination.js"></script>
	<script src="angularjs/utils/checklist-model.js"></script>
	<script src="angularjs/utils/ui-bootstrap-tpls-1.3.3.min.js"></script>
	<script src="modules/bootstrap-modal.js"></script>	
	<script src="modules/bootstrap-notify.js"></script>
	<script src="modules/account.js"></script>
	
	<script src="js/Sortable.js"></script>
	<script src="modules/ng-sortable.js"></script>	
	
	<script src="jspdf/jspdf.min.js"></script>
	<script src="jspdf/faker.min.js"></script>
	<script src="jspdf/jspdf.plugin.autotable.src.js"></script>
  </body>
</html>