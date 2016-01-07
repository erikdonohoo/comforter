'use strict';

angular.module('comforter', [
	'ngSanitize',
	'comforter.routes',
	'ngMaterial'
])

.run([
	'$rootScope',
	'$http',
function ($scope, $http) {
	// Expose app version info
	$http.get('version.json').success(function (v) {
		$scope.version = v.version;
		$scope.appName = v.name;
	});
}]);

angular.module('comforter.templates', []);
