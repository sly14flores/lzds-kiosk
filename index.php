<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<link rel="icon" href="favicon.ico">
    <title>LZDS</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

	<style type="text/css">
	
		.row {

			width: 100%;
			background: url('image/NewBG.png') no-repeat;
			background-size:350px 300px;
			background-position: 950px 450px;
		
		}
		
		.welcome {
			margin-top: 15%;
			margin-left: auto;
			margin-right: auto;
		}
		
		.notification {
			margin-top: 30%;
			margin-left: auto;
			margin-right: auto;
		}
		
		.welcome p, .notification p, .guest-log p {
			margin: 0;
			line-height: 1.2em;
			text-align: center;
		}
		
		#profile-pic {
			
			margin-top: 10%;
			box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
			
		}
		
		#profile-info {
			
			margin-top: 10%;
		
		}
		
		#profile-info {
			
		}
		
		.profile-info-header {
			
			font-size: 2em;
		
		}
		
		.profile-info-body p {

			font-size: 3em;
			margin: 0;
			line-height: 1em;
		
		}
		
		.guest-log input {
		
			font-size: 25px;
		
		}
		
		[data-notify="message"] {
			font-size: 2em;
		}
		
		#dBox {
			height: 500px;
			top: calc(50% - 250px) !important;
		}		
		
		#purposes {
		
			font-size: 25px;
			height: 70px;
		
		}
		
		#footer{
			padding: 20px;
			text-align: center;
			background: url('image/Notice to LZDS guest.jpeg') no-repeat fixed 50% 50%;
			margin-top: 20px;
		}
		
	</style>
	
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body ng-app="appMon" ng-controller="appMonCtrl">

	<div class="container-fluid">
		<div id="content" class="row"></div>
	</div>
  
	<div id="dBox" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="label-dBox">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="label-dBox">Select Purpose</h4>
		  </div>
		  <div class="modal-body">
			<div class="form-group">
				<select id="purpose" class="form-control"></select>
				<p class="help-block" style="margin-top: 15px; font-size: 18px; color: #ff7272;">If purpose is not listed select "Other"</p>
			</div>
		  </div>
		  <div class="modal-footer">
		  </div>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->  
  
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="jquery/jquery-2.2.4.min.js"></script>
    <script src="jquery/jquery.rfid.js"></script>
	<script src="admin/angularjs/angular.min.js"></script>	
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="bootstrap-notify-3.1.3/bootstrap-notify.min.js"></script>
	<script type="text/javascript">
	
	function screenSaver() {

		$('#content').load('screensaver.php', function() {
			
			setInterval(timeTick,1000);
			
		});	

	}
	
	function timeTick() {
		
		var d = new Date();	
		$('#time').html(d.toLocaleTimeString());
		
	}
	
	var getProfile = function(rfid) {
		
		$.ajax({
			url: 'profile-type.php',
			type: 'post',
			data: {rfid: rfid},
			success: function(data, status) {
				
				$('#content').load('profile.php?rfid='+rfid+'&profile_type='+data,function() {
					
					if (data == 'Guest') {						
						
						var selectPurpose = false;
						var showGuestPurposes = new guestPurposes();						
						
						$.ajax({
							url: 'guest-instance.php',
							type: 'post',
							data: {rfid: rfid},
							success: function(data, status) {

								if (data == 'In') {
								
									$.rfidscan({enabled: false});
									$('#guest_purpose').focus(function() {
										showGuestPurposes.show(function() { selectPurpose = true; showGuestPurposes.purposes(); });
									});
									$('#logGuest').unbind();
									$('#logGuest').click(function() {
										if ($('#guest_name').val() == '') {
											$.notify({message: 'Please enter your name'}, {type: 'danger', newest_on_top: true});
											$('#guest_name').focus();
											return;
										}
										if ($('#guest_purpose').val() == '') {
											$.notify({message: 'Please enter your purpose'}, {type: 'danger', newest_on_top: true});
											$('#guest_purpose').focus(function() {
												showGuestPurposes.show(function() { selectPurpose = true; showGuestPurposes.purposes(); });
											});
											$('#guest_purpose').focus();										
											return;
										}
										/*
										** cache guest last instance guest info`
										*/
										// localStorage.removeItem($('#cacheGuestInfo')[0].dataset.guestRfid);
										localStorage.setItem($('#cacheGuestInfo')[0].dataset.guestRfid, JSON.stringify({guest_name: $('#guest_name').val(), guest_purpose: $('#guest_purpose').val()}));
									
										logGuest({guest_log_id: $('#cacheGuestInfo')[0].dataset.guestLogId, guest_name: $('#guest_name').val(), guest_purpose: $('#guest_purpose').val()},data);
									});
									
									$(document).unbind('keypress');
									$(document).keypress(function(e){
										if (e.which == 13) {
											if (selectPurpose) {
												var purpose = $('#purpose').val();
												if (purpose == '-') {
													$.notify({message: 'Please select purpose'}, {type: 'danger', newest_on_top: true});
												}
												if ((purpose != '-') && (purpose != 'other')) {
													showGuestPurposes.hide(function() { $('#guest_purpose').focus(); selectPurpose = false; });
													$('#guest_purpose').unbind('focus');
													$('#guest_purpose').val($('#purpose').val());
												}
												if (purpose == 'other') {
													showGuestPurposes.hide(function() { $('#guest_purpose').focus(); selectPurpose = false; });
													$('#guest_purpose').unbind('focus');													
												}
											}
											if (($('#logGuest')[0]) && (!selectPurpose)) {								
												$('#logGuest').click();
											}
										}
									});									
								
								} else {
					
									var guestRfidInfo = JSON.parse(localStorage[$('#cacheGuestInfo')[0].dataset.guestRfid]);
									logGuest({guest_log_id: $('#cacheGuestInfo')[0].dataset.guestLogId, guest_name: guestRfidInfo['guest_name'], guest_purpose: guestRfidInfo['guest_purpose']},data);										
									// setTimeout(screenSaver, 2000);						
								
								}
						
						}
						
						});
					
					
					} else {
					
						setTimeout(screenSaver, 2000);
						
					}
					
				});				
				
			}
		});
	
	};
	
	function guestPurposes() {
		
		this.purposes = function() {
			
			$.ajax({
				url: 'guests-purposes.php',
				type: 'GET',
				success: function(data, status) {
					$('#purpose').html(data);
				}
			});
			
		}
		
		this.show = function(fn) {
			$('#dBox').modal({backdrop: 'static', keyboard: false});
			$('#dBox').on('shown.bs.modal', function (e) {
				fn();
				$('#purpose').focus();
			});
		};
		
		this.hide = function(fn) {
			$('#dBox').modal('hide');
			$('#dBox').on('hidden.bs.modal', function (e) {
				fn();
			});			
		};
	
	}
	
	// Parses raw scan into name and ID number
	var rfidParser = function(rawData) {
		// console.log(rawData, rawData.length);
		if (rawData.length != 11) return null;
		else return rawData;
		
	};

	// Called on a bad scan (company card not recognized)
	var badScan = function() {
		console.log("Bad Scan.");
	};

	// Initialize the plugin.
	$.rfidscan({
		parser: rfidParser,
		success: getProfile,
		error: badScan
	});
	
	function reInitScan() {
		$.rfidscan({
			enabled: true
		});	
	};
	
	function logGuest(guest,mode) {
	
		$.ajax({
			url: 'guest-info.php',
			type: 'post',
			data: guest,
			success: function(data, status) {
				// setTimeout(function() { location.reload(); }, 1000);
				if (mode == 'In') {
					$.notify({message: 'Guest info recorded.'}, {type: 'success'});
					reInitScan();
				} else {
					$.notify({message: 'Guest has logout.'}, {type: 'success'});
				}
				setTimeout(function() {
					screenSaver();
				}, 2000);				
			}
		});
		
	}
	
	var app = angular.module('appMon',[]);
	
	app.service('sendQueues', function($http) {
		
		this.start = function(scope, queues) {
			
			var qCounts = queues.length;

			if (qCounts > 0) {
				var i = 0;
				sendQueue(queues[0]);
			} else {
				return false;
			}		
			
			function sendQueue(queue) {

				if (i < qCounts) {
				
					$http({
					  method: 'POST',
					  url: 'http://localhost:8080/send',
					  data: queue
					}).then(function mySucces(response) {
						
						sendQueue(queues[++i]);
						
					}, function myError(response) {
						 
						scope.togCollect();
						
					});
				
				} else {
				
					scope.togCollect();
					
				}
				
			}
			
		}
	

	});
	
	app.controller('appMonCtrl', function($scope, $http, $interval, sendQueues) {

		screenSaver();
		
		$('.row').css('height',$(document).height());
		
		$scope.queues = function() {
		
			$http({
			  method: 'POST',
			  url: 'ajax.php',
			  data: {r: 'collect_queues'},
			}).then(function mySucces(response) {
				
				if (response.data.length > 0) {
					$interval.cancel($scope.collectQueues);
					sendQueues.start($scope, response.data);
				}
				
			}, function myError(response) {
				 
			  // error
				
			});
		
		}
		
		$scope.togCollect = function() {
		
			$scope.collectQueues = $interval(function() {
			
				$scope.queues();
			
			}, 60000);

		}
		
		$scope.togCollect();
	
	});	
	
	</script>
  </body>
</html>