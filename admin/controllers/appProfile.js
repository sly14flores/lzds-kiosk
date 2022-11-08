var app = angular.module('appProfile', ['angularUtils.directives.dirPagination', 'checklist-model', 'ui.bootstrap', 'bootstrap-modal','account-module','school-year']);

app.directive('fileModel', ['$parse', function ($parse) {
	return {
	   restrict: 'A',
	   link: function(scope, element, attrs) {
		  var model = $parse(attrs.fileModel);
		  var modelSetter = model.assign;
		  
		  element.bind('change', function(){
			 scope.$apply(function(){
				modelSetter(scope, element[0].files[0]);
			 });
		  });

		  // scope.$watch(attrs.fileModel, function(file) {
			// $('#'+element['context']['id']).val(null);
		  // });
	   }
	};
}]);

app.service('fileUpload', ['$http', function ($http) {
	this.uploadFileToUrl = function(file, uploadUrl, scope){
	   var fd = new FormData();
	   fd.append('file', file);
	
        var xhr = new XMLHttpRequest();
        xhr.upload.addEventListener("progress", uploadProgress, false);
        xhr.addEventListener("load", uploadComplete, false);
        xhr.open("POST", uploadUrl)
        scope.progressVisible = true;
        xhr.send(fd);
	   
		// upload progress
		function uploadProgress(evt) {
			scope.uploadingFile = true;
			scope.$apply(function(){
				scope.progress = 0;				
				if (evt.lengthComputable) {
					scope.progress = Math.round(evt.loaded * 100 / evt.total);
				} else {
					scope.progress = 'unable to compute';
				}
				scope.view.profilePicture = "../profile-pics/avatar.png";
			});
		}

		function uploadComplete(evt) {
			/* This event is raised when the server send back a response */
			scope.$apply(function(){			
				$http.get('controllers/appProfile.php?r=get_profile_picture&id='+scope.profile.id).success(function(data) {

					scope.view.profilePicture = data;
					scope.uploadingFile = false;
				
				});
			});			

			$('#proPic').val(null);
		}

	}
}]);

app.service('appProfileService', function($http, $compile) {
	
	var self = this;

	self.list = function($scope) {
		
		self.bUI();
		
		$scope.addBtn = false;
		$scope.editBtn = false;
		$scope.delBtn = false
		$scope.searchIn = false;
		
		$scope.activeTemplate = "views/appProfileList.html?ver=1.0";
	
		$scope.currentPage = 1;
		$scope.pageSize = 10;
	
		$http({
		  method: 'POST',
		  url: 'controllers/appProfile.php?r=profiles',
		  data: $scope.filter
		}).then(function mySucces(response) {
		
			$scope.results = response.data;
			$.unblockUI();
			
		}, function myError(response) {
			 
		  // error
			
		});		
			
	};	
	
	self.bUI = function(msg = 'Please wait...') {
		
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
	};
	
	self.uUI = function() {
		
		$.unblockUI();
		
	};
	
});

app.controller('appProfileCtrl', function($scope, $http, appProfileService, fileUpload, bootstrapModal, schoolYear) {
	
	$scope.profiles = [];
	
	$scope.filter = {};
	$scope.filter.sy = {id:0,school_year:'All'};
	$scope.filter.profile_type = {name: "All", description: "All"};

	$scope.view = {};	

	$scope.view.profileTypes = [
		{name: "All", description: "All"},
		{name: "Student", description: "Student"},
		{name: "Staff", description: "Staff"},
		{name: "Guest", description: "Guest"}
	];	
	
	$scope.view.chkUnchk = false;
	
	$scope.view.proPic = null;
	
	$scope.frmHolder = {};	
	$scope.frmProfile = {};
	
	function validate(scope,form) {	
			
		var controls = scope.frmHolder[form].$$controls;

		angular.forEach(controls,function(elem,i) {

			if (elem.$$attr.$attr.required) elem.$touched = elem.$invalid;
								
		});

		return scope.frmHolder[form].$invalid;			
		
	};
	
	$scope.profileList = function() {
		
		if ($scope.view.chkUnchk) $scope.view.chkUnchk = false;
		appProfileService.list($scope);	
	
	}
	
	$scope.profileTypeChange = function() {		
		
		if ($scope.profile.profile_type == 'Student') {
			
			var url = 'https://app.lzds.edu.ph/handlers/get-sid.php';
			// var url = 'http://192.168.0.20/lzds/handlers/get-sid.php';
			// var url = 'http://localhost/lzds/handlers/get-sid.php';
			$http.get(url).then(function mySucces(response) {
				$scope.fids = response.data;
			}, function myError(response) {
				$scope.fids = [];
			});
			
		};
		
		if (($scope.profile.profile_type == 'Staff') || ($scope.profile.profile_type == 'Guest')) {
			
			$scope.fids = [];
			
		};		
		
	}
	
	$scope.uploadFile = function(){
	   // $scope.proPic = null;
	   var file = $scope.view.proPic;
	   
	   if (file == undefined) return;
	   console.log(file);
	   
	   var pp = file['name'];
	   var en = pp.substring(pp.indexOf("."),pp.length);
	   
	   var uploadUrl = "controllers/appProfile.php?r=upload_profile_picture&id="+$scope.profile.id+"&en="+en;
	   fileUpload.uploadFileToUrl(file, uploadUrl, $scope);
	}
	
	$scope.loadFormProfile = function() {

		appProfileService.bUI();
		
		$scope.view.mode = 'New';		
		
		$scope.uploadingFile = false;
		$scope.progress = 0;
		
		$scope.addBtn = true;
		$scope.editBtn = true;
		$scope.delBtn = true
		$scope.searchIn = true;	
	
		$scope.buttons = {
			titles: {
				ok: 'Save',
				cancel: 'Cancel'
			}
		};		
	
		$scope.activeTemplate = "views/profile.html?ver=1.0";		
		
		$scope.profile = {};
		$scope.account = {};
		$scope.view.newProfile = true;

		$scope.view.profilePicture = "../profile-pics/avatar.png";	
		
		$scope.profileTypes = {
			"Student": "Student",
			"Staff": "Staff",
			"Guest": "Guest"
		};
		
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
			"Genesis": "Genesis",
			"Omega": "Omega",
			"Alpha": "Alpha",
			"Faith": "Faith",
			"Love": "Love",
			"Joy": "Joy",
			"Peace": "Peace",
			"Hope": "Hope",
			"Charity": "Charity",
			"St. Matthew": "St. Matthew",
			"St. Mark": "St. Mark",
			"St. Luke": "St. Luke",
			"St. John": "St. John",
			"St. Peter": "St. Peter",
			"St. Andrew (ABM)": "St. Andrew (ABM)",
			"St. James (GAS)": "St. James (GAS)",
			"St. James (STEM)": "St. James (STEM)",
			"St. Andrew (HUMSS)": "St. Andrew (HUMSS)",
			"St. Paul (GAS)": "St. Paul (GAS)",
			"St. Paul (ABM)": "St. Paul (ABM)",
			"St. Paul (STEM)": "St. Paul (STEM)", 
			"St. Paul (HUMSS)": "St. Paul (HUMSS)",
		};
		
		$http({
		  method: 'POST',
		  url: 'controllers/appProfile.php?r=new_profile',
		  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then(function mySucces(response) {
		
			$scope.profile.id = response.data;
			$scope.account.profile_id = response.data;
			appProfileService.uUI();
			
		}, function myError(response) {
			 
		  // error
			
		});
		
		$scope.idSelected = function(item, model, label, event) {
			$scope.profile.last_name = item['enrollee_lname'];
			$scope.profile.first_name = item['enrollee_fname'];
			$scope.profile.middle_name = item['enrollee_mname'];
			$scope.profile.gender = item['gender'];
			$scope.profile.level = item['enrollee_grade'];
			$scope.profile.section = item['student_section'];
			$scope.profile.cp = item['enrollee_contact'];
		}

	}
	
	$scope.confirmProfileAction = function() {

		if (validate($scope,'frmProfile')) return;
		
		if ($scope.view.newProfile) {
			
			bootstrapModal.confirm($scope,'Confirm add new profile?','profileSave()');
			
		} else {
			
			bootstrapModal.confirm($scope,'Confirm update profile info?','profileSave()');
			
		}
		
	}
	
	$scope.confirmAccountAction = function() {

		if (validate($scope,'frmAccount')) return;
			
		bootstrapModal.confirm($scope,'Update account info?','accountSave()');
		
	}	
	
	$scope.profileSave = function() {
		
		appProfileService.bUI();
		
		if (!$scope.view.newProfile) delete $scope.profile.picture;
	
		$http({
		  method: 'POST',
		  url: 'controllers/appProfile.php?r=save_profile',
		  data: $scope.profile,
		  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then(function mySucces(response) {
		
			bootstrapModal.closeConfirm();
			// $scope.profileList();
			appProfileService.uUI();			
			var notify = ($scope.view.newProfile) ? 'New profile successfully added' : 'Profile info successfully updated.';
			bootstrapModal.notify(notify);
			
			$scope.buttons = {
				titles: {
					ok: 'Update',
					cancel: 'Close'
				}
			};
			
			$scope.view.newProfile = false;	
			
		}, function myError(response) {
			 
		  // error
			
		});			
		
	}
	
	$scope.accountSave = function() {
		
		appProfileService.bUI();
	
		$http({
		  method: 'POST',
		  url: 'controllers/appProfile.php?r=save_account',
		  data: $scope.account,
		  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
		}).then(function mySucces(response) {
		
			bootstrapModal.closeConfirm();
			// $scope.profileList();
			appProfileService.uUI();			
			var notify = 'Account info successfully updated.';
			bootstrapModal.notify(notify);
			
			$scope.buttons = {
				titles: {
					ok: 'Update',
					cancel: 'Close'
				}
			};

			$scope.view.newProfile = false;
			
		}, function myError(response) {
			 
		  // error
			
		});			
		
	}	
	
	$scope.editProfile = function() {
		
		appProfileService.bUI();

		$scope.view.mode = 'Edit';		
		
		if ($scope.profiles.length > 1) return;
		
		$scope.uploadingFile = false;
		$scope.progress = 0;
		
		$scope.addBtn = true;
		$scope.editBtn = true;
		$scope.delBtn = true
		$scope.searchIn = true;	
	
		$scope.buttons = {
			titles: {
				ok: 'Update',
				cancel: 'Close'
			}
		};	
	
		$scope.activeTemplate = "views/profile.html";		
		
		$scope.profile = {};
		$scope.view.newProfile = false;
		
		$scope.view.profilePicture = "../profile-pics/avatar.png";
		
		$scope.profileTypes = {
			"Student": "Student",
			"Staff": "Staff",
			"Guest": "Guest"
		};
		
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
			method: 'GET',
			url: 'controllers/appProfile.php',
			params: {r: 'edit_profile', id: $scope.view.profiles.ids[0]}
		}).then(function success(response) {
			
			$scope.view.profiles.ids = [];
			$scope.view.profilePicture = response.data['profile']['picture'];
			$scope.profile = response.data['profile'];
			$scope.account = response.data['account'];
			
			$scope.profileTypeChange();
			
			appProfileService.uUI();			
			
		}, function error(response) {
			
		});
	
	}
	
	$scope.cancelProfile = function() {
		
		appProfileService.bUI();
		
		if ($scope.view.newProfile) {

			$http({
			  method: 'POST',
			  url: 'controllers/appProfile.php?r=cancel_profile',
			  data: { id: $scope.profile.id },
			  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(function mySucces(response) {
				$scope.profileList();
			}, function myError(response) { 
			
			});	
		
		} else {
			$scope.profileList();
		}
	
	}
	
	$scope.confirmDelProfile = function() {

		if ($scope.view.profiles.ids.length == 0) {
			bootstrapModal.notify('Please select one.');
			return;
		}
		
		bootstrapModal.confirm($scope,'Confirm delete profile(s)?','delProfile()');
	
	}
	
	$scope.delProfile = function() {
		
			appProfileService.bUI();

			var ids = $scope.view.profiles.ids.toString();
			
			$http({
			  method: 'POST',
			  url: 'controllers/appProfile.php?r=cancel_profile',
			  data: { id: ids },
			  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(function mySucces(response) {
				bootstrapModal.closeConfirm();
				$scope.view.profiles.ids = [];	
				$scope.profileList();
				bootstrapModal.notify('Profile(s) successfully deleted.');
			},
			function myError(response) {
				
			});		
		
	}
	
	$scope.view.profiles = {};
	$scope.view.profiles.ids = [];
	$scope.view.profiles.cacheIds = [];
	
	$scope.selections = function() {

		if ($scope.view.chkUnchk) {
			$scope.view.profiles.ids = angular.copy($scope.view.profiles.cacheIds);
		} else {
			$scope.view.profiles.ids = [];
		}

	}
	
	$scope.cacheId = function(id,currentPage) {
		
		if ($scope.view.chkUnchk) $scope.view.chkUnchk = false;
		$scope.view.profiles.ids = [];
		
		if ($scope.currentPage != currentPage) $scope.view.profiles.cacheIds = []; // reset if page changes
		
		$scope.view.profiles.cacheIds.push(id);
		
		$scope.currentPage = currentPage;
		
	}
	
	$scope.chkSelected = function() { // handles set main checkbox to true or false if all are selected or one is unchecked
		
		if (($scope.view.profiles.ids).length != ($scope.view.profiles.cacheIds).length) $scope.view.chkUnchk = false;
		else $scope.view.chkUnchk = true;
		
	}
	
	/*
	** Delete unsaved profiles
	*/
	$http({
	  method: 'GET',
	  url: 'controllers/appProfile.php?r=check_unsaved_profile',
	  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function mySucces(response) {
	
		appProfileService.list($scope);
		
	}, function myError(response) {
		 
	  // error
		
	});
	
	/* school years */
	schoolYear.get($scope);
	
  
});

