'use strict';

function appDirective(api, $parse) {
	return {
		templateUrl: 'modules/app/app.directive.tpl.html',
		link: function ($scope, elem, attr) {
			api.getApp($parse(attr.appId)($scope), $parse(attr.projectName)($scope)).then(function (app) {
				$scope.app = app;
			});
		}
	};
}

angular.module('comforter.app')
.directive('app', ['apiService', '$parse', appDirective]);
