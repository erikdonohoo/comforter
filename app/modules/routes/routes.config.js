angular.module('comforter.routes')

.config([
	'$routeProvider',
	'$locationProvider',
function ($routeProvider, $locationProvider) {
	'use strict';

	$routeProvider.otherwise('/');
	$locationProvider.html5Mode({
		enabled: true
	});
}]);
