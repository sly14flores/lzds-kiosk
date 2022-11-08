var app = angular.module('appPreferences', ['angularUtils.directives.dirPagination', 'checklist-model', 'ui.bootstrap', 'bootstrap-modal','account-module']);

app.service('appPreferencesService', function($http, $compile, $timeout) {
	
	this.dBox = function(scope,title,body,ok,shown = null,hidden = null) {
	
		$('#dBox').modal('show');
		$('#dBox').on('shown.bs.modal', function (e) {

		});
		$('#dBox').on('hidden.bs.modal', function (e) {

		});
		$('#label-dBox').html(title);
		
		$http.get(body).then(function(response) {
			$('#dBox .modal-body').html(response.data);
			$compile($('#dBox')[0])(scope);
		});				
		
		var buttons = '<button type="button" class="btn btn-primary" ng-click="'+ok+'">Save</button>';		
			buttons += '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';

		$('#dBox .modal-footer').html(buttons);
	
	}
	
	this.closedBox = function() {
		$('#dBox').modal('hide');
	}
	
	this.dBoxSub = function(scope,title,body,ok,shown = null,hidden = null) {
	
		$('#dBoxSub').modal('show');
		$('#dBoxSub').on('shown.bs.modal', function (e) {

		});
		$('#dBoxSub').on('hidden.bs.modal', function (e) {

		});
		$('#label-dBoxSub').html(title);
		
		$http.get(body).then(function(response) {
			$('#dBoxSub .modal-body').html(response.data);
			$compile($('#dBoxSub')[0])(scope);
		});				
		
		var buttons = '<button type="button" class="btn btn-primary" ng-click="'+ok+'">Save</button>';		
			buttons += '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';

		$('#dBoxSub .modal-footer').html(buttons);
	
	}	
	
	this.closedBoxSub = function() {
		$('#dBoxSub').modal('hide');
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

app.controller('appPreferencesCtrl', function($scope, $http, $timeout, $compile, appPreferencesService, bootstrapModal) {

	$scope.views = {};
	
	$scope.guestPurposes = function() {
		
		$scope.purposes = [];
		$scope.purposesDelete = [];
		appPreferencesService.dBox($scope,'Guest Log Purposes','views/guestPurposesForm.html','saveGuestPurposes()');
		
		$scope.views.purposeInvalid = false;
		
		$http({
		  method: 'POST',
		  url: 'controllers/appPreferences.php?r=load',
		  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then(function mySucces(response) {
		
			$scope.purposes = response.data;
			
		}, function myError(response) {
			 
		  // error
			
		});			
		
	};
	
	$scope.addGuestPurpose = function() {

		if (($scope.views.frmGuestPurposes.purpose.$untouched) || ($scope.views.frmGuestPurposes.purpose.$invalid)) {
			$scope.views.purposeInvalid = true;
			return;
		}
		
		$scope.purposes.push({disabled: true, invalid: false, id: 0, description: $scope.views.purpose});

		$scope.views.purpose = '';
		$scope.views.purposeInvalid = false;		
	
	};
	
	$scope.enableEditPurpose = function(item) {
		
		var index = $scope.purposes.indexOf(item);

		if (item.description == '') {
			$scope.purposes[index].invalid = true;
			return;
		}
		$scope.purposes[index].invalid = false;
		$scope.purposes[index].disabled = !$scope.purposes[index].disabled;
	
	};
	
	$scope.deleteGuestPurpose = function(item) {
		
		var index = $scope.purposes.indexOf(item);
		$scope.purposes.splice(index, 1);
		
		if (item.id > 0) {
			$scope.purposesDelete.push(item.id);
		}
		
	};
	
	$scope.saveGuestPurposes = function() {
		
		/*
		** if id is 0 add otherwise update
		*/

		appPreferencesService.closedBox();
		
		$scope.purposesAdd = [];
		$scope.purposesUpdate = [];
		
		$scope.purposes.forEach(function(item,i) {
			var index = $scope.purposes.indexOf(item);
			delete $scope.purposes[index]['disabled'];
			delete $scope.purposes[index]['invalid'];
			if (item.id == 0) {
				$scope.purposesAdd.push($scope.purposes[index]);
			} else {
				$scope.purposesUpdate.push($scope.purposes[index]);
			}
		});

		$http({
		  method: 'POST',
		  url: 'controllers/appPreferences.php?r=save',
		  data: {purposesAdd: $scope.purposesAdd, purposesUpdate: $scope.purposesUpdate, purposesDelete: $scope.purposesDelete},
		  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then(function mySucces(response) {
		
			console.log(response.data);
			
		}, function myError(response) {
			 
		  // error
			
		});		
		
	};
	
		
	function staffsSchedules(scope) {
		
		var self = this;
		
		self.show = function() {			
			
			// fetch schedules
			scope.schedulesDelete = [];			
			appPreferencesService.dBox(scope,'Staffs Schedules','views/staffsSchedules.html','staffsSchedules.saveSchedules()');
			self.schedules();
			
		};
		
		self.schedules = function() {
	
			$http({
			  method: 'POST',
			  url: 'controllers/appPreferences.php?r=schedules',
			  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(function mySucces(response) {

				scope.schedules = response.data;
				
			}, function myError(response) {
				 
			  // error
				
			});		
		
		}
		
		self.schedule = function() {

			appPreferencesService.dBoxSub(scope,'Schedule','views/staffSchedule.html','staffsSchedules.save()');
			
			scope.views.scheduleInvalid = false;
			
			scope.schedule = {
				id: 0,
				description: '',
				details: [
					{id: 0, schedule_id: 0, day: 'Monday', morning_in: new Date("0"), morning_out: new Date("0"), afternoon_in: new Date("0"), afternoon_out: new Date("0"), dayoff: 0},
					{id: 0, schedule_id: 0, day: 'Tuesday', morning_in: new Date("0"), morning_out: new Date("0"), afternoon_in: new Date("0"), afternoon_out: new Date("0"), dayoff: 0},
					{id: 0, schedule_id: 0, day: 'Wednesday', morning_in: new Date("0"), morning_out: new Date("0"), afternoon_in: new Date("0"), afternoon_out: new Date("0"), dayoff: 0},
					{id: 0, schedule_id: 0, day: 'Thursday', morning_in: new Date("0"), morning_out: new Date("0"), afternoon_in: new Date("0"), afternoon_out: new Date("0"), dayoff: 0},
					{id: 0, schedule_id: 0, day: 'Friday', morning_in: new Date("0"), morning_out: new Date("0"), afternoon_in: new Date("0"), afternoon_out: new Date("0"), dayoff: 0},
					{id: 0, schedule_id: 0, day: 'Saturday', morning_in: new Date("0"), morning_out: new Date("0"), afternoon_in: new Date("0"), afternoon_out: new Date("0"), dayoff: 0},
					{id: 0, schedule_id: 0, day: 'Sunday', morning_in: new Date("0"), morning_out: new Date("0"), afternoon_in: new Date("0"), afternoon_out: new Date("0"), dayoff: 0}
				]
			}
		
		};
		
		self.save = function() {
			
			scope.views.scheduleInvalid = false;
			if ((scope.schedule.description == '') || (scope.schedule.description == undefined)) {
				scope.views.scheduleInvalid = true;
				return;
			}
			
			$http({
			  method: 'POST',
			  url: 'controllers/appPreferences.php?r=saveStaffSchedule',
			  data: scope.schedule,
			  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(function mySucces(response) {
				
				appPreferencesService.closedBoxSub();
				self.schedules();
				
			}, function myError(response) {
				 
			  // error
				
			});
			
		};
		
		self.close = function() {
			
			appPreferencesService.closedBox();
			
		};
		
		self.edit = function(schedule) {
			
			appPreferencesService.bUI();
			$http({
			  method: 'POST',
			  url: 'controllers/appPreferences.php?r=editStaffSchedule',
			  data: {id: schedule.id},
			  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(function mySucces(response) {
				
				appPreferencesService.dBoxSub(scope,'Schedule','views/staffSchedule.html','staffsSchedules.save()');				
				scope.schedule = response.data;
				
				angular.forEach(response.data.details,function(item,i) {
					response.data.details[i]['morning_in'] = new Date("2000-01-01 "+item['morning_in']);
					response.data.details[i]['morning_out'] = new Date("2000-01-01 "+item['morning_out']);
					response.data.details[i]['afternoon_in'] = new Date("2000-01-01 "+item['afternoon_in']);
					response.data.details[i]['afternoon_out'] = new Date("2000-01-01 "+item['afternoon_out']);
				});
						
				$timeout(function() {
					scope.schedule = response.data;
					appPreferencesService.uUI();
				},500);				
				
			}, function myError(response) {
				 
			  // error
				
			});			
			
		};
		
		self.del = function(schedule) {
			
			var index = scope.schedules.indexOf(schedule);
			scope.schedules.splice(index, 1);
			
			if (schedule.id > 0) {
				scope.schedulesDelete.push(schedule.id);
			}
			
		};
		
		self.saveSchedules = function() {
			
			$http({
			  method: 'POST',
			  url: 'controllers/appPreferences.php?r=deleteSchedule',
			  data: {id: scope.schedulesDelete},
			  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(function mySucces(response) {

				self.close();
				
			}, function myError(response) {
				 
			  // error
				
			});			

		};
		
	};
	
	$scope.staffsSchedules = new staffsSchedules($scope);
  
});

