'use strict';

function homeRouteConfig ($routeProvider) {
	$routeProvider
		.when('/', {
			templateUrl: 'modules/routes/home/home.route.html',
			controller: ['$scope', '$routeParams',
				function ($scope, $routeParams) {
					$scope.$routeParams = $routeParams;
				}]
		});
}

angular.module('comforter.routes.home')
.config(['$routeProvider', homeRouteConfig]);
