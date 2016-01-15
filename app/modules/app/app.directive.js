'use strict';

function appDirective() {
	return {
		templateUrl: 'modules/app/app.directive.tpl.html',
		link: function ($scope) {
			$scope.stuff = 'This is the app directive';
		}
	};
}

angular.module('comforter.app')
.directive('app', [appDirective]);
