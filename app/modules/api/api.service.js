'use strict';

var statusMap = {
	success: 'good',
	failed: 'bad'
};

var gitlabApi = 'https://gitlab.goreact.com/api/v3';

var apiService = function ($http, $q) {
	this.$http = $http;
	this.$q = $q;
	this.appCache = {};
};

// add status, most recent commit and list of commits for ease of use
var modifyApp = function (app) {
	app.commitList = makeCommitList(app);
	app.mostRecentCommit = app.commitList.reduce(function (mostRecent, commit) {
		var createdAt = new Date(commit.created_at);
		return mostRecent < createdAt ? createdAt : mostRecent;
	}, new Date(1900, 1, 1));
	app.coverageStatus = statusMap[app.mostRecentCommit.status];
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

apiService.prototype.getApp = function (appId) {
	var $q = this.$q;
	var $http = this.$http;
	var cache = this.appCache;

	return cache[appId] ? $q.when(cache[appId]) : $q.all({
		gitlab: $http.get(gitlabApi + '/projects/' + appId),
		comforter: $http.get('/api/apps/' + appId)
	}).then(function (appInfo) {
		return $q.when(cache[appId] = modifyApp(angular.merge(appInfo.gitlab.data, appInfo.comforter.data)));
	});
};

angular.module('comforter.api')
.service('apiService', ['$http', '$q', apiService]);
