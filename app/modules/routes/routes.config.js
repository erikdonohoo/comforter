angular.module('comforter.routes')

.config([
	'$urlRouterProvider',
function ($urlRouterProvider) {
	'use strict';

	$urlRouterProvider.otherwise('/');
}]);
