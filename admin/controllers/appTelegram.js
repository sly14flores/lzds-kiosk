var app = angular.module('appTelegram',['account-module','bootstrap-modal']);

app.controller('appTelegramCtrl', function($scope, $http, bootstrapModal) {

  $scope.activeTemplate = "views/appTelegrams.html";

  $scope.frmHolder = {};
  $scope.frmAnnounce = {};
  $scope.announcement = {};

  $scope.validation = {
    chat_id_invalid: false
  }

  const bUI = (msg = 'Please wait...') => {

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

  const uUI = () => {

    $.unblockUI();

  }

	function validate(scope,form) {	
			
		var controls = scope.frmHolder[form].$$controls;

		angular.forEach(controls,function(elem,i) {

			if (elem.$$attr.$attr.required) elem.$touched = elem.$invalid;
								
		});

		return scope.frmHolder[form].$invalid;			
		
	};

	$scope.confirmSend = function() {

    $scope.validation.chat_id_invalid = false;

		if (validate($scope,'frmAnnounce')) return;

		bootstrapModal.confirm($scope,'Send Announcement?','sendAnnouncement()');
		
	}

  $scope.sendAnnouncement = function() {

    $scope.validation.chat_id_invalid = false;

    bootstrapModal.closeConfirm();

    bUI('Sending announcement');

		$http({
		  method: 'POST',
		  url: 'controllers/sendAnnouncement.php',
		  data: $scope.announcement
		}).then(function mySucces(response) {
		
			
			uUI();
			
		}, function myError(response) {
			 
		  // error
      uUI();
			
		});	

  }

  $scope.sendTest = function() {

    $scope.validation.chat_id_invalid = false;

    if (($scope.announcement.chat_id === undefined) || ($scope.announcement.chat_id === '')) {

      $scope.validation.chat_id_invalid = true;

      return

    }

    bUI('Sending announcement (Test)');

    $.ajax({
      url: 'https://api.telegram.org/bot5910632478:AAFvQtx_zMPYzhJmOXKuQWlD6GLuHhOC_Tk/sendMessage',
      type: 'post',
      data: {
        text: $scope.announcement.content,
        chat_id: $scope.announcement.chat_id,
      },
      success: function(data, status) {

        uUI()

      },
      error: function() {

        uUI()

      }
    });

  }
  
  $scope.refresh = () => {

    $scope.chats = [];

    const url = 'https://api.telegram.org/bot5910632478:AAFvQtx_zMPYzhJmOXKuQWlD6GLuHhOC_Tk/getUpdates';

    $http.get(url).then(function mySucces(response) {
      $scope.chats = response?.data?.result;
      console.log(response?.data?.result);
    }, function myError(response) {
      $scope.chats = [];
    });

  };

  $scope.refresh();

});