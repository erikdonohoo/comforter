'use strict';

var apiService = function ($http) {
	this.$http = $http;
};

apiService.prototype.getApps = function () {
	return this.$http.get('/api/apps');
};

angular.module('comforter.api')
.service('apiService', ['$http', apiService]);
