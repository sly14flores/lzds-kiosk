angular.module('bootstrap-notify',[]).service('bootstrapNotify', function() {

	this.show = function(msg) {
		
		$.notify({
			message: msg
		});
		
	}

});