'use strict';

var statusMap = {
	success: 'good',
	failed: 'bad'
};

var apiService = function ($http, $q) {
	this.$http = $http;
	this.$q = $q;
	this.appCache = {};
};

// add status, most recent commit and list of commits for ease of use
var modifyApp = function (app) {
	app.coverageStatus = statusMap[app.mostRecentCommit = (app.commitList = (makeCommitList(app))
		.reduce(function (mostRecent, commit) {
		var createdAt = new Date(commit.created_at);
		return mostRecent < createdAt ? createdAt : mostRecent;
	}, new Date(1900, 1, 1))).status]; // some date that is guaranteed to be older than any build run
	app.modified_at = new Date(app.modified_at);
	app.created_at = new Date(app.created_at);
	return app;
};

var makeCommitList = function (app) {
	if (app.commitList && angular.isArray(app.commitList)) { return app.commitList; }

	return Object.keys(app.commits).map(function (key) {
		return app.commits[key];
	});
};

apiService.prototype.getApps = function () {
	return this.appCache.apps ? this.$q.when(this.appCache.apps) : this.$http.get('/api/apps').then(function (response) {
		return (this.appCache.apps = response.data.map(modifyApp));
	}.bind(this));
};

angular.module('comforter.api')
.service('apiService', ['$http', '$q', apiService]);
