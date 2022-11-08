var app = angular.module('appDTR', ['angularUtils.directives.dirPagination', 'checklist-model', 'ui.bootstrap', 'bootstrap-notify','account-module','bootstrap-modal','ng-sortable']);

app.directive('profileTypeSelect', function($http) {

	return {
	   restrict: 'A',
	   link: function(scope, element, attrs) {

		  element.bind('change', function(){
				
				var pt = element[0].value;
				
				scope.$apply(function(){
					scope.filter = {};
					scope.filter.year = (new Date()).getFullYear();					
					scope.views.filterByDisabled = false;
					$http.get('controllers/appDTR.php?r=fullname_fids&profile_type='+pt).then(function(response){
						scope.views.rfids = response.data;
					});					
				});

				switch (pt) {
				
					case "Staffs":
						scope.$apply(function(){
							scope.views.filterBys = {
								"Payroll Period": "payroll_period",
								"Month": "month",								
								"Specific Date": "specific_date"
							};
							scope.activeTemplate = "views/appDTRStaffs.html?ver=1.0";
							scope.results = [];							
						});
					break;
					
					case "Students":
						scope.$apply(function(){
							scope.views.filterBys = {
								"Specific Date": "specific_date",
								"Month": "month"
							};
							scope.activeTemplate = "views/appDTRStudents.html?ver=1.0";
							scope.results = [];							
						});
					break;
					
					case "Guests":
						scope.$apply(function(){
							scope.views.filterBys = {
								"Specific Date": "specific_date",
								"Month": "month"
							};
							scope.activeTemplate = "views/appDTRGuests.html?ver=1.0";
							scope.results = [];							
						});
					break;
					
					default:
						scope.$apply(function(){
							scope.views.filterBys = {};
							scope.views.filterByDisabled = true;
							scope.views.periodDisabled = true;
							scope.views.dateSpecificDisabled = true;
							scope.views.monthDisabled = true;
							scope.views.rfids = [];
						});						
					break;
				
				}				

		  });

	   }
	};

});

app.directive('filterBySelect', function() {

	return {
	   restrict: 'A',
	   link: function(scope, element, attrs) {

			element.bind('change', function() {

				scope.$apply(function() {
					scope.views.periodDisabled = true;
					scope.views.dateSpecificDisabled = true;
					scope.views.monthDisabled = true;				
				});
				
				var fb = element[0].value;
				
				switch (fb) {
					
					case "payroll_period":
						scope.$apply(function() {
							scope.views.periodDisabled = false;
							scope.views.monthDisabled = false;							
						});					
					break;
					
					case "specific_date":
						scope.$apply(function() {
							scope.views.dateSpecificDisabled = false;
						});
					break;
					
					case "month":
						scope.$apply(function() {
							scope.views.monthDisabled = false;
						});
					break;

				}
				
			});
	   
	   }
	};

});

app.directive('printSf', function(bootstrapNotify) {

	return {
		restrict: 'A',
		link: function(scope, element, attrs) {			
			
			element.bind('click', function() {
				
/* 				var filter = scope.filter;

				if (filter == undefined) {
					bootstrapNotify.show('No DTR record found, please view one');
					return;
				}
				
				switch (scope.views.profileType) {
					
					case "Students":
					
						if ((scope.views.filterBy == undefined) || (scope.views.filterBy == '')) {
							bootstrapNotify.show('Please select filter');
							return;
						};

						if (scope.views.filterBy == 'specific_date') {
							bootstrapNotify.show('Please set Filter By to Month then select month');
							return;							
						}
						
						if ((filter.month == undefined) || (filter.month == '')) {
							bootstrapNotify.show('Please select month');							
							return;
						}
					
					break;
				
				}
				
				if (filter['rfid'] == '') {
					bootstrapNotify.show('Please select fullname');
					return;
				}
				if (filter['year'] == '') {
					bootstrapNotify.show('Please enter year');
					return;
				} */

			});

	   }
	};

});

app.directive('logs', function($http,$timeout,bootstrapModal) {
	
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {			
			
			element.bind('click', function() {
				
				var d = attrs.logs;
				
				scope.views.profile_type = 'Staff';
				if (scope.views.profileType == 'Students') scope.views.profile_type = 'Student';
				
				scope.sortableConfig = { group: 'log', animation: 150 };
				'Start End Add Update Remove Sort'.split(' ').forEach(function (e) {
					scope.sortableConfig['on' + e] = function() {
						
						if (e == 'Sort') {
							
							// what to do on sort
							angular.forEach(scope.logs, function(item,i) {
								item.log_order = i+1;
							});
							
							// update orders
							$timeout(function() {
								
								$http({
								  method: 'POST',
								  url: 'controllers/appDTR.php?r=save_multi_logs',
								  data: {logs: scope.logs, date: d},
								  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
								}).then(function mySucces(response) {
														
								}, function myError(response) {
									 
								  // error
									
								});								
								
							},500);

							
						}			
						
					};
				});			

				var rfid = scope.filter.rfid;
				scope.views.logDate = (new Date(d)).toDateString();			
				
				scope.views.noTimeLog = false;
				scope.views.time_log = '';
				
				scope.logOrders = {
					"": 0,
					"Morning In": 1,
					"Morning Out": 2,
					"Afternoon In": 3,
					"Afternoon Out": 4					
				};
				
				$http({
				  method: 'POST',
				  url: 'controllers/appDTR.php?r=time_logs',
				  data: {date: d, rfid: rfid},
				  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then(function mySucces(response) {
					
					scope.logs = response.data;
					
				}, function myError(response) {
					 
				  // error
					
				});
				
				scope.addTimeLog = function() {
					
					scope.views.noTimeLog = false;				
					if (scope.views.time_log == '') {
						scope.views.noTimeLog = true;
						return;
					}
					
					$http({
					  method: 'POST',
					  url: 'controllers/appDTR.php?r=add_log',
					  data: {rfid: rfid, time_log: d + ' ' + scope.views.time_log, sms: 'queue'},
					  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function mySucces(response) {

						$http({
						  method: 'POST',
						  url: 'controllers/appDTR.php?r=time_logs',
						  data: {date: d, rfid: rfid},
						  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
						}).then(function mySucces(response) {
							
							scope.logs = response.data;
							
						}, function myError(response) {
							 
						  // error
							
						});
					
					}, function myError(response) {
						 
					  // error
						
					});						
					
					// scope.logs.push({id: 0, log_order: 0, time_log: scope.views.time_log, disabled: true, invalid: false});
					
				}
				
				scope.editSaveLog = function(log) {
					
					var index = scope.logs.indexOf(log);

					if (log.time_log == '') {
						scope.logs[index].invalid = true;
						return;
					}
					
					scope.logs[index].invalid = false;
					scope.logs[index].disabled = !scope.logs[index].disabled;
					
					if (scope.logs[index].disabled) {
						
						$http({
						  method: 'POST',
						  url: 'controllers/appDTR.php?r=save_log',
						  data: {log: log, date: d},
						  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
						}).then(function mySucces(response) {
												
						}, function myError(response) {
							 
						  // error
							
						});						
						
					}
				
				}
				
				scope.delTimeLog = function(log) {
					
					var id = log.id;
					
					var index = scope.logs.indexOf(log);
					scope.logs.splice(index, 1);
					
					$http({
					  method: 'POST',
					  url: 'controllers/appDTR.php?r=delete_log',
					  data: {id: [id]},
					  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function mySucces(response) {
						
						
					}, function myError(response) {
						 
					  // error
						
					});					
				
				}
				
				scope.changeOrder = function(log) {
					
					$http({
					  method: 'POST',
					  url: 'controllers/appDTR.php?r=change_log_order',
					  data: {log: log, date: d},
					  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function mySucces(response) {
											
					}, function myError(response) {
						 
					  // error
						
					});						
					
				}
				
				bootstrapModal.show(scope,'Time Logs','views/logs.html',function() {
					
				}, function() {
					
				});
				
			});
			
			
			
		}
	};
	
});

app.service('appDTR', function($http, $compile) {

	this.init = function($scope) {

		$scope.activeTemplate = "views/appDTR.html?ver=1.0";

		$scope.views.profileTypes = {
			"Staffs": "Staffs",
			"Students": "Students",
			"Guests": "Guests"
		};

		$scope.views.periods = {
			"First Period": "first_period",
			"Second Period": "second_period"
		};

		$scope.views.months = {
			"January": "01",
			"February": "02",
			"March": "03",
			"April": "04",
			"May": "05",
			"June": "06",
			"July": "07",
			"August": "08",
			"September": "09",
			"October": "10",
			"November": "11",
			"December": "12"
		};	
		
		$scope.filter.dateSpecific = new Date();

		$scope.dateOpen = function() {
			$scope.views.popupDate.opened = true;
		};
		
		$scope.views.popupDate = {
			opened: false
		};
		
		$scope.views.format = 'shortDate';
		
		$scope.filter.year = (new Date()).getFullYear();

		$scope.views.filterByDisabled = true;
		$scope.views.periodDisabled = true;
		$scope.views.dateSpecificDisabled = true;
		$scope.views.monthDisabled = true;

	}
	
	this.validate = function($scope) {

	}

	this.bUI = function(msg = 'Please wait...') {

		$.blockUI({
			message: '<span style="font-size: 12px;">'+msg+'</span>',
			css: {
			border: 'none',
			padding: '15px',
			backgroundColor: '#000',
			'-webkit-border-radius': '10px',
			'-moz-border-radius': '10px',
			opacity: .5,
			color: '#fff'
			}
		});
	}

	this.uUI = function() {

		$.unblockUI();

	}

});

app.controller('appDTRCtrl', function($scope, $http, $timeout, appDTR, bootstrapModal, bootstrapNotify) {

	$scope.views = {};
	$scope.views.togPrint = false;	
	$scope.filter = {};
	
	$scope.filter.rfid = '';
	$scope.filterStr = '';
	
	$scope.report = {};
	
	$scope.report.nolevel = false;
	$scope.report.nosection = false;
	$scope.report.noyear = false;
	$scope.report.nomonth = false;
	
	$scope.report.filter = {};
	$scope.report.filter.year = (new Date()).getFullYear();
	
	$scope.studentLevels = {
		"Nursery": "Nursery",
		"Kindergarten": "Kindergarten",
		"Grade 1": "Grade 1",
		"Grade 2": "Grade 2",
		"Grade 3": "Grade 3",
		"Grade 4": "Grade 4",
		"Grade 5": "Grade 5",
		"Grade 6": "Grade 6",
		"Grade 7": "Grade 7",
		"Grade 8": "Grade 8",
		"Grade 9": "Grade 9",
		"Grade 10": "Grade 10",
		"Grade 11": "Grade 11",
		"Grade 12": "Grade 12"
	};

	$scope.studentSections = {
		"Alpha": "Alpha",
		"Charity": "Charity",
		"Faith": "Faith",
		"Hope": "Hope",
		"Joy": "Joy",
		"Love": "Love",
		"Omega": "Omega",
		"Peace": "Peace",
		"St. John": "St. John",
		"St. Luke": "St. Luke",
		"St. Mark": "St. Mark",
		"St. Matthew": "St. Matthew",
		"St. Peter": "St. Peter"
	};	
	
	
	
	$http({
	  method: 'POST',
	  url: 'controllers/appDTR.php?r=select_staffs',
	  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function mySucces(response) {

		$scope.views.staffs = response.data;

	},
	function myError(response) {

	});
	
	appDTR.init($scope);
	
	$scope.fetchDTR = function() {

		// basic validation
		if (($scope.filter.fullname == undefined) || ($scope.filter.fullname == '')) $scope.filter.rfid = '';		
		if ($scope.views.profileType == undefined) {
			bootstrapNotify.show('Please select Profile Type');
			return;
		}
		if ($scope.views.filterBy == undefined) {
			bootstrapNotify.show('Please select Filter By');		
			return;
		}		
		if ( ($scope.filter.fullname == undefined) && ($scope.views.profileType != 'Guests') ) {
			bootstrapNotify.show('Please specify Full Name');		
			return;
		}
		if ( ($scope.filter.fullname == '') && ($scope.views.profileType != 'Guests') ) {
			bootstrapNotify.show('Please specify Full Name');		
			return;
		}		
		if ($scope.filter.year == '') {
			bootstrapNotify.show('Please specify Year');
			return;
		}

		// specific validation
		switch ($scope.views.profileType) {
		
			case "Staffs":
				
				if (($scope.views.filterBy == "month") && ($scope.filter.month == undefined)) {
					
					bootstrapNotify.show('Please select Month');
					return;					
				
				}
				
				if (($scope.views.filterBy == "payroll_period") && ($scope.filter.month == undefined)) {
					
					bootstrapNotify.show('Please select Month');
					return;					
				
				}

				if (($scope.views.filterBy == "payroll_period") && ($scope.filter.period == undefined)) {
					
					bootstrapNotify.show('Please select Payroll Period');
					return;					
				
				}

				if (($scope.views.filterBy == "specific_date") && ($scope.filter.dateSpecific == undefined)) {
					
					bootstrapNotify.show('Please select date');
					return;					
				
				}				
				
			break;
			
			default:
			
				if (($scope.views.filterBy == "month") && ($scope.filter.month == undefined)) {
					
					bootstrapNotify.show('Please select Month');
					return;					
				
				}

				if (($scope.views.filterBy == "specific_date") && ($scope.filter.dateSpecific == undefined)) {
					
					bootstrapNotify.show('Please select date');
					return;					
				
				}				
			
			break;
		
		}
		
		switch ($scope.views.filterBy) {
			
			case "payroll_period":
				
				delete $scope.filter.dateSpecific;
				
			break;
			
			case "specific_date":
				
				delete $scope.filter.period;
				delete $scope.filter.month;				
				
			break;
			
			case "month":

				delete $scope.filter.period;			
				delete $scope.filter.dateSpecific;			
				
			break;
			
			default:
			
				delete $scope.filter.period;			
				delete $scope.filter.dateSpecific;
				delete $scope.filter.month;			
				
			break;
			
		}		
		
		appDTR.bUI();
		
		$http({
		  method: 'POST',
		  url: 'controllers/appDTR.php?r='+$scope.views.profileType,
		  data: $scope.filter,
		  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then(function mySucces(response) {

			switch ($scope.views.profileType) {
				
				case "Staffs":
				
					$scope.activeTemplate = "views/appDTRStaffs.html?ver=1.0";
				
				break;
				
				case "Students":
				
					$scope.activeTemplate = "views/appDTRStudents.html?ver=1.0";				
				
				break;
				
				case "Guests":
					
					$scope.currentPage = 1;
					$scope.pageSize = 50;
					$scope.activeTemplate = "views/appDTRGuests.html?ver=1.0";				
				
				break;
			
			}
		
			$scope.results = response.data;
			appDTR.uUI();
			
		}, function myError(response) {
			 
		  // error
			
		});		

	}
	
	$scope.fullnameSelected = function(item, model, label, event) {
		
		$scope.filter.fullname = item['fullname'];
		$scope.filter.rfid = item['rfid'];
	
	}

	$scope.selectReport = function(type) {
		
		$scope.report.filter = {};
		
		switch (type) {
			
			case "Staffs":
				$scope.views.reportFilter = "views/staffsReport.html";
			break;
			
			case "Students":
				$scope.views.reportFilter = "views/studentsReport.html";
			break;
			
			case "Guests":
				$scope.views.reportFilter = "views/guestsReport.html";			
			break;
			
			default:
				$scope.views.reportFilter = "";
			break;
			
		}

	}

	$scope.printStudentsReport = function() {

		$scope.report.nolevel = false;
		$scope.report.nosection = false;
		$scope.report.noyear = false;
		$scope.report.nomonth = false;		
		
		if (($scope.report.filter.level == undefined) || ($scope.report.filter.level == '')) {
			$scope.report.nolevel = true;
			return;
		}
		
		if (($scope.report.filter.section == undefined) || ($scope.report.filter.section == '')) {
			$scope.report.nosection = true;
			return;
		}		
		
		if (($scope.report.filter.year == undefined) || ($scope.report.filter.year == '')) {
			$scope.report.noyear = true;
			return;
		}		
		
		if (($scope.report.filter.month == undefined) || ($scope.report.filter.month == '')) {
			$scope.report.nomonth = true;
			return;
		}

		// $scope.views.togPrint = false;
		var filter = $scope.report.filter;		
		appDTR.bUI('Analyzing attendances please wait...');
		
		// form 2 excel
		$scope.filterStr = JSON.stringify(filter);
		$timeout(function() { $('#form2excel').submit(); },500);
		appDTR.uUI();		
		return;
		
 		$http({
		  method: 'POST',
		  url: 'controllers/appDTR.php?r=student_attendance_report_jspdf',
		  data: filter,
		  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then(function mySucces(response) {	
			
			appDTR.uUI();
			var doc = new jsPDF({
			  orientation: 'landscape',
			  unit: 'pt',
			  format: [612, 936]
			});
		
			var totalPagesExp = "{total_pages_count_string}";

			var pageContent = function (data) {
				// HEADER
				doc.setFontSize(12);
				doc.setTextColor(40);
				doc.setFontStyle('normal');
				doc.text("Daily Attendance Report of Learners", data.settings.margin.left, 30);
				doc.setFontSize(10);
				doc.setTextColor(10);
				doc.text("Level: "+filter.level+",", data.settings.margin.left, 45);
				doc.text("Section: "+filter.section, data.settings.margin.left+110, 45);
				doc.text(Object.keys($scope.views.months)[parseInt(filter.month)-1]+" "+filter.year, data.settings.margin.left, 60);
				
				// FOOTER
				var str = "Page " + data.pageCount;
				// Total page number plugin only available in jspdf v1.0+
				if (typeof doc.putTotalPages === 'function') {
					str = str + " of " + totalPagesExp;
				}
				doc.setFontSize(10);
				doc.text(str, data.settings.margin.left, doc.internal.pageSize.height - 10);
				
			};
			
			// var data = response.data.rows;
			doc.autoTable(response.data.columns, response.data.rows, {
				theme: 'grid',
				margin: {top: 70},
				startY: 70,
				drawRow: function (row, data) {
				
				},
				drawCell: function (cell, data) {
					if (data.column.dataKey === 'lastCell') {
						if (data.row.index === 0) {
							doc.rect(cell.x, cell.y, cell.width / 2, cell.height, 'S');			
							doc.autoTableText('Absent', (cell.x + cell.width / 2) - (cell.width / 4), cell.y + cell.height / 2, {
								halign: 'center',
								valign: 'middle'
							});
							doc.rect(cell.x + (cell.width / 2), cell.y, cell.width / 2, cell.height, 'S');
							doc.autoTableText('Tardy', (cell.x + cell.width / 2) + (cell.width / 2) - (cell.width / 4), cell.y + cell.height / 2, {
								halign: 'center',
								valign: 'middle'
							});							
						} else {
							doc.rect(cell.x, cell.y, cell.width / 2, cell.height, 'S');			
							doc.autoTableText(response.data.rows[data.row.index]['absent'], (cell.x + cell.width / 2) - (cell.width / 4), cell.y + cell.height / 2, {
								halign: 'center',
								valign: 'middle'
							});
							doc.rect(cell.x + (cell.width / 2), cell.y, cell.width / 2, cell.height, 'S');
							doc.autoTableText(response.data.rows[data.row.index]['tardy'], (cell.x + cell.width / 2) + (cell.width / 2) - (cell.width / 4), cell.y + cell.height / 2, {
								halign: 'center',
								valign: 'middle'
							});
						}
							
						return false;						
					}
				},
				addPageContent: pageContent				
			});

			// Total page number plugin only available in jspdf v1.0+
			if (typeof doc.putTotalPages === 'function') {
				doc.putTotalPages(totalPagesExp);
			}
		
			doc.output('dataurlnewwindow');
				
		},
		function myError(response) {

		});
		
		
	}
	
	$scope.printGuestsReport = function() {
		
		$scope.report.noyear = false;		
		$scope.report.nomonth = false;

		if (($scope.report.filter.year == undefined) || ($scope.report.filter.year == '')) {
			$scope.report.noyear = true;
			return;
		}
		
		if (($scope.report.filter.month == undefined) || ($scope.report.filter.month == '')) {
			$scope.report.nomonth = true;
			return;
		}

		var filter = $scope.report.filter;	

 		$http({
		  method: 'POST',
		  url: 'controllers/appDTR.php?r=guests_dtr_jspdf',
		  data: filter,
		  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then(function mySucces(response) {

			var doc = new jsPDF({
			  orientation: 'portrait',
			  unit: 'pt',
			  format: [612, 792]
			});

			var totalPagesExp = "{total_pages_count_string}";

			var pageContent = function (data) {
				// HEADER
				doc.setFontSize(12);
				doc.setTextColor(40);
				doc.setFontStyle('normal');
				doc.text("Guests Daily Time Record", data.settings.margin.left, 30);
				doc.setFontSize(10);
				doc.setTextColor(10);
				doc.text(Object.keys($scope.views.months)[parseInt(filter.month)-1]+" "+filter.year, data.settings.margin.left, 45);
				
				// FOOTER
				var str = "Page " + data.pageCount;
				// Total page number plugin only available in jspdf v1.0+
				if (typeof doc.putTotalPages === 'function') {
					str = str + " of " + totalPagesExp;
				}
				doc.setFontSize(10);
				doc.text(str, data.settings.margin.left, doc.internal.pageSize.height - 10);
				
			};

			doc.autoTable(response.data.columns, response.data.rows, {
				theme: 'grid',
				addPageContent: pageContent,
				margin: {top: 60}
			});			
			
			// Total page number plugin only available in jspdf v1.0+
			if (typeof doc.putTotalPages === 'function') {
				doc.putTotalPages(totalPagesExp);
			}
		
			doc.output('dataurlnewwindow');

		},
		function myError(response) {

		});		
		
	}
	
	function getKey(obj,value) {

		var key = '';

		angular.forEach(obj, function(d,i) {

			if (d === value) key = i;

		});

		return key;

	}
	
	$scope.printStaffsReport = function() {
		
		$scope.report.noyear = false;		
		$scope.report.nomonth = false;
		$scope.report.nostaff = false;

		if (($scope.report.filter.year == undefined) || ($scope.report.filter.year == '')) {
			$scope.report.noyear = true;
			return;
		}
		
		if (($scope.report.filter.month == undefined) || ($scope.report.filter.month == '')) {
			$scope.report.nomonth = true;
			return;
		}
		
		if (($scope.report.filter.staff == undefined) || ($scope.report.filter.staff == '')) {
			$scope.report.nostaff = true;
			return;
		}

		var filter = $scope.report.filter;		
		
 		$http({
		  method: 'POST',
		  url: 'controllers/appDTR.php?r=staffs_dtr_jspdf',
		  data: filter,
		  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then(function mySucces(response) {			

			var doc = new jsPDF({
			  orientation: 'portrait',
			  unit: 'pt',
			  format: [612, 936]
			});

			var totalPagesExp = "{total_pages_count_string}";

			var pageContent = function (data) {
				// HEADER
				doc.setFontSize(12);
				doc.setTextColor(40);
				doc.setFontStyle('normal');
				doc.text("Staff Daily Time Record", data.settings.margin.left, 30);
				doc.setFontSize(11);
				doc.setTextColor(10);
				doc.text(getKey($scope.views.staffs,filter.staff), data.settings.margin.left, 45);		
				doc.setFontSize(10);				
				doc.text(Object.keys($scope.views.months)[parseInt(filter.month)-1]+" "+filter.year, data.settings.margin.left, 60);
				
				// FOOTER
				var str = "Page " + data.pageCount;
				// Total page number plugin only available in jspdf v1.0+
				if (typeof doc.putTotalPages === 'function') {
					str = str + " of " + totalPagesExp;
				}
				doc.setFontSize(10);
				doc.text(str, data.settings.margin.left, doc.internal.pageSize.height - 10);
				
			};

			// doc.autoTable(response.data.columns, response.data.rows, {
			doc.autoTable(response.data.columns, response.data.rows, {
				theme: 'grid',
				addPageContent: pageContent,
				margin: {top: 80}
			});			
			
			// Total page number plugin only available in jspdf v1.0+
			if (typeof doc.putTotalPages === 'function') {
				doc.putTotalPages(totalPagesExp);
			}
		
			doc.output('dataurlnewwindow');

		},
		function myError(response) {

		});				
		
	}
	
});