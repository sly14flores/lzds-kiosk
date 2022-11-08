angular.module('bootstrap-modal',[]).service('bootstrapModal', function($compile,$timeout) {

	this.confirm = function(scope,body,ok,shown = null,hidden = null) {
		
		$('#confirm').modal('show');
		$('#confirm').on('shown.bs.modal', function (e) {
		  // do something...
		});
		$('#confirm').on('hidden.bs.modal', function (e) {
		  // do something...
		});
		$('#label-confirm').html('Confirmation');
		$('#confirm .modal-body').html(body);

		var buttons = '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
			buttons += '<button type="button" class="btn btn-primary" ng-click="'+ok+'">Ok</button>';
		$('#confirm .modal-footer').html(buttons);
		$compile($('#confirm .modal-footer')[0])(scope);
		
	}
	
	this.closeConfirm = function() {
		$('#confirm').modal('hide');
	}
	
	this.notify = function(body,shown = null,hidden = null) {
		
		$('#notify').modal('show');
		$('#notify').on('shown.bs.modal', function (e) {
		  // do something...
		});
		$('#notify').on('hidden.bs.modal', function (e) {
		  // do something...
		});
		$('#label-notify').html('Notification');
		$('#notify .modal-body').html(body);

		var buttons = '<button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>';
		$('#notify .modal-footer').html(buttons);
		
	}
	
	this.show = function(scope,title,body,shown = null,hidden = null) {
		
		$('#modal-show').modal('show');
		$('#modal-show').on('shown.bs.modal', function (e) {
		  // do something...
		});
		$('#modal-show').on('hidden.bs.modal', function (e) {
		
			hidden();
		  
		});
		$('#label-modal-show').html(title);
		$('#modal-show .modal-body').load(body);

		var buttons = '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>';
		$('#modal-show .modal-footer').html(buttons);
		
		$timeout(function() {
			$compile($('#modal-show .modal-body')[0])(scope);
		},500);

	}

});