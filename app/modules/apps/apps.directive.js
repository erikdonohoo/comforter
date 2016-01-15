'use strict';

function appsDirective(api) {

	return {
		templateUrl: 'modules/apps/apps.directive.tpl.html',
		link: function ($scope) {

			api.getApps().then(function (apps) {
				$scope.apps = apps;
			});
		}
	};
}

angular.module('comforter.apps')
.directive('apps', ['apiService', appsDirective]);
