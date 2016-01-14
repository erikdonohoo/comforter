'use strict';

var restler = require('restler');
var q = require('q');
var settings = require('../settings.json');
var gitlab = settings.gitlab;

// communicate with gitlab
module.exports = {

	// helper states
	states: {
		PENDING: 'pending',
		RUNNING: 'running',
		SUCCESS: 'success',
		FAILED: 'failed',
		CANCELLED: 'cancelled'
	},

	// get default branch
	getDefaultBranch: function (projectId) {

		var deferred = q.defer();

		restler.get(gitlab.api + 'projects/' + projectId + '?private_token=' + gitlab.token)

		.on('success', function (data) {
			deferred.resolve(data.default_branch);
		})

		.on('fail', deferred.reject.bind(deferred))
		.on('error', deferred.reject.bind(deferred));

		return deferred.promise;
	},

	// update commit status
	updateCommitStatus: function (data) {

		var deferred = q.defer();
		if (!data.id || !data.sha || !data.state) {
			deferred.reject({error: 'missing required params (id, sha, state)'});
		}

		console.log(gitlab.api + 'projects/' + data.id + '/statuses/' + data.sha + '?private_token=' + gitlab.token);
		console.log(data);
		restler.post(gitlab.api + 'projects/' + data.id + '/statuses/' + data.sha + '?private_token=' + gitlab.token, {
			data: data
		})

		.on('success', deferred.resolve.bind(deferred))

		.on('fail', deferred.reject.bind(deferred))
		.on('error', deferred.reject.bind(deferred));

		return deferred.promise;
	}
};
