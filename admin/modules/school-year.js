angular.module('school-year',[]).factory('schoolYear',function($http) {
	
	function schoolYear() {
		
		var self = this;
		
		self.get = function(scope) {
			
			$http({
			  method: 'POST',
			  url: 'handlers/school-years.php'
			}).then(function mySucces(response) {

				scope.school_years = response.data['school_years'];
				scope._school_years = response.data['_school_years'];
				// scope.filter.sy = response.data['school_year'];

			}, function myError(response) {

			  // error

			});
			
		};
		
	};
	
	return new schoolYear();
	
});