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
	getDefaultBranch: function (projectId, token, tokenType) {

		var deferred = q.defer();

		restler.get(gitlab.api + 'projects/' + projectId + '?' + tokenType + '=' + token)

		.on('success', function (data) {
			deferred.resolve(data.default_branch);
		})

		.on('fail', deferred.reject.bind(deferred))
		.on('error', deferred.reject.bind(deferred));

		return deferred.promise;
	},

	// update commit status
	updateCommitStatus: function (data, token, tokenType) {

		var deferred = q.defer();
		if (!data.id || !data.sha || !data.state) {
			deferred.reject({error: 'missing required params (id, sha, state)'});
		}

		restler.post(gitlab.api + 'projects/' + data.id + '/statuses/' + data.sha + '?' + tokenType + '=' + token, {
			data: data
		})

		.on('success', deferred.resolve.bind(deferred))

		.on('fail', deferred.reject.bind(deferred))
		.on('error', deferred.reject.bind(deferred));

		return deferred.promise;
	},

	gitlabAuth: function (req, res, next) {
		if (!req.headers.authorization && !(req.query && req.query.apiKey)) {
			return res.status(401).json({error: 'you need to authenticate through gitlab.goreact.com or send an api key'});
		}

		var token, tokenType;
		if (req.headers.authorization) {
			token = req.headers.authorization.split('Bearer ')[1];
			tokenType = 'access_token';
		} else {
			token = req.query.apiKey;
			tokenType = 'private_token';
		}

		restler.get(gitlab.api + 'user?' + tokenType + '=' + token)

		.on('success', function () {
			req.auth_token = token;
			req.token_type = tokenType;
			next();
		})

		.on('fail', function (err) {
			return res.status(401).json({error: 'you need to authenticate through gitlab.goreact.com', gitlab: err});
		})
		.on('error', function (err) {
			return res.status(401).json({error: 'you need to authenticate through gitlab.goreact.com', gitlab: err});
		});
	}
};
