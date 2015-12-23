'use strict';

function homeRouteConfig ($stateProvider) {
	$stateProvider
		.state('home', {
			url: '/',
			templateUrl: 'modules/routes/home/home.route.html',
			controller: ['$scope', '$stateParams',
				function ($scope, $stateParams) {
					$scope.$stateParams = $stateParams;
				}
			]
		});

}

angular.module('comforter.routes.home')
.config(['$stateProvider', homeRouteConfig]);
