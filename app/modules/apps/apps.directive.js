'use strict';

function appsDirective() {
	return {
		templateUrl: 'modules/apps/apps.directive.tpl.html',
		link: function ($scope) {
			$scope.stuff = 'This is the apps directive';
		}
	};
}

angular.module('comforter.apps')
.directive('apps', [appsDirective]);
