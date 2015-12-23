'use strict';

angular.module('comforter.routes').requires.push('comforter.routes.apps');
angular.module('comforter.routes.apps', [
	'comforter.templates',
	'comforter.routes',
	'ngRoute'
]);
