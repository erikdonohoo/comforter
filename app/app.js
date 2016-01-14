'use strict';

angular.module('comforter', [
	'ngSanitize',
	'comforter.routes',
	'ngMaterial',
	'ng-auth',
	'comforter.apps'
])

.config([
	'$oauth2Provider',
function ($oauth2) {
	$oauth2.configure({
		clientId: 'a7aadfdaaf92c048d8845c5579204356522066167e59a03e5b6278af917c82b2',
		oauth2Url: 'https://gitlab.goreact.com/oauth/authorize',
		tokenUrl: '/oauth/token',
		autoAuth: true,
		contentUrls: ['/api', 'https://gitlab.goreact.com/api/v3'],
		redirectUri: true,
		responseType: 'code',
		pathDelimiter: '?'
	});
}])

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
