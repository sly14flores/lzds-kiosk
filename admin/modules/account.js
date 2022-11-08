angular.module('account-module',['bootstrap-modal']).directive('logoutAccount', function($window,bootstrapModal) {

	return {
		restrict: 'A',
		link: function(scope, element, attrs) {

			element.bind('click', function() {
					
				bootstrapModal.confirm(scope,'Are you sure you want to logout?','logout()');

			});
			
			scope.logout = function() {
				
				$window.location.href = 'logout.php';
				
			};
	   
		}
	};

});