'use strict';

function appsRouteConfig ($routeProvider) {
		$routeProvider
		.when('/apps/:id/:name', {
			templateUrl: 'modules/routes/apps/apps.route.html',
			controller: ['$scope', '$routeParams',
				function ($scope, $routeParams) {
					$scope.$routeParams = $routeParams;
				}
			]
		});
}

angular.module('comforter.routes.apps')
.config(['$routeProvider', appsRouteConfig]);
