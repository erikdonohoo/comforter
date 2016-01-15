'use strict';

function appsDirective(api) {

	return {
		templateUrl: 'modules/apps/apps.directive.tpl.html',
		link: function ($scope) {
			
			api.getApps().then(function (response) {
				$scope.apps = response.data;
			});

			$scope.coverageStatus = api.coverageStatus;
		}
	};
}

angular.module('comforter.apps')
.directive('apps', ['apiService', appsDirective]);
