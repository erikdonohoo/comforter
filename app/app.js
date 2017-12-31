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
		clientId: '02d2f13e45d1202394e25cc0e48d8b48d93d2c63193a28051aea5892aa594ecc',
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
