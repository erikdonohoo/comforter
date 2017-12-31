'use strict';

angular.module('comforter', [
	'ngSanitize',
	'comforter.routes',
	'ngMaterial',
	'ng-auth',
	'comforter.apps'
])

.constant('gitlabHost', 'http://192.168.33.52')

.config([
	'$oauth2Provider',
	'gitlabHost',
function ($oauth2, gitlabHost) {
	$oauth2.configure({
		clientId: '718feddfcb4c5a8417df78ba87da72c108ec78bf4da92e2a4a42728256aa89cf',
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
