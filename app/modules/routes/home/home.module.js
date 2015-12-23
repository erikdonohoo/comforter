'use strict';

angular.module('comforter.routes').requires.push('comforter.routes.home');
angular.module('comforter.routes.home', [
	'comforter.templates',
	'comforter.routes',
	'ui.router'
]);
