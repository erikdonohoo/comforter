'use strict';

var statusMap = {
	success: 'good',
	failed: 'bad'
};

var apiService = function ($http) {
	this.$http = $http;
};

var makeCommitList = function (app) {
	if (app.commitList && angular.isArray(app.commitList)) { return app.commitList; }

	return Object.keys(app.commits).map(function (key) {
		return app.commits[key];
	});
};

apiService.prototype.getApps = function () {
	return this.$http.get('/api/apps');
};

// determine if most recent coverage run was good or bad
apiService.prototype.coverageStatus = function (app) {
	return app.coverageStatus || (app.coverageStatus = statusMap[(app.commitList = makeCommitList(app)).reduce(function (mostRecent, commit) {
		var createdAt = new Date(commit.created_at);
		return mostRecent < createdAt ? createdAt : mostRecent;
	}, new Date(2000, 1, 1)).status]); // some date sooner than anything we care about
};

angular.module('comforter.api')
.service('apiService', ['$http', apiService]);
