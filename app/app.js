'use strict';

angular.module('comforter', [
	'ngSanitize',
	'comforter.routes',
	'ngMaterial',
	'ng-auth',
	'comforter.apps'
])

.constant('gitlabHost', 'https://gitlab.goreact.com')

.config([
	'$oauth2Provider',
	'gitlabHost',
function ($oauth2, gitlabHost) {
	$oauth2.configure({
		clientId: 'a7aadfdaaf92c048d8845c5579204356522066167e59a03e5b6278af917c82b2',
		oauth2Url: gitlabHost + '/oauth/authorize',
		tokenUrl: '/oauth/token',
		autoAuth: true,
		contentUrls: ['/api', gitlabHost + '/api/v4'],
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
	$http.get('version.json').then(function (response) {
		var v = response.data;
		$scope.version = v.version;
		$scope.appName = v.name;
	});
}]);

angular.module('comforter.templates', []);
