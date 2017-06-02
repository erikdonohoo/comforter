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
		clientId: '21aba965cd47dbbeb352221ad879a6944ab1409547d2036f2be6ea9f41eb8089',
		oauth2Url: gitlabHost + '/oauth/authorize',
		tokenUrl: '/oauth/token',
		autoAuth: true,
		contentUrls: ['/api', gitlabHost + '/api/v3'],
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
