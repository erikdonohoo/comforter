'use strict';

var gitlabHelper = require('./gitlab.js');
var settings = require('../settings.json');
var App = require('../models/app');
var q = require('q');

var buildFailed = function (data) {
	gitlabHelper.updateCommitStatus({
		id: data.project,
		sha: data.commit,
		state: gitlabHelper.states.FAILED,
		ref: data.branch,
		name: settings.gitlab.commitBuildStatusName,
		description: data.description || 'Uh oh, the coverage couldn\'t be handled'
	});
};

// responsible for updating build commit status on github and saving information
// to the mongodb about each update

// - If any step after 3, but before X fails, update commit status with failure
// 2. Get coverage %
// 3. Determine via GitLab API which branch of this project is default
// 4. Get last known coverage for that branch in this project
// 5. If this coverage is less than last known, it is a failure.
// 6. Report discrepancy on commit status fail with target_url to the zip of coverage (if included)
// 7. Otherwise pass and update commit status with success and target_url to zip (if included)
module.exports = function (data) {

	// Tell GitLab via commit status a job has begun
	gitlabHelper.updateCommitStatus({
		id: data.project,
		sha: data.commit,
		state: gitlabHelper.states.RUNNING,
		ref: data.branch,
		name: settings.gitlab.commitBuildStatusName,
		description: 'Comforter is handling the build'
	}, data.token, data.tokenType).catch(function () {
		buildFailed(data);
	})

	// Get default branch
	// for now, default branch against which coverage is tracked is always master
	.then(q('master'))

	// Get last known coverage of the branch
	.then(getLastKnownCoverageOfBranch.bind(null, data.project))

	// get the coverage diff
	.then(compareCoverage.bind(null, data.coverage))

	// Send success status with target_url path to coverage
	.then(function (diff) {

		// coverage drop
		var description, state;
		if (diff < 0) {
			description = 'Coverage was decreased by ' + (diff * -1) + '%';
			state = gitlabHelper.states.FAILED;
		} else {
			description = 'Coverage is increased by ' + diff + '%';
			state = gitlabHelper.states.SUCCESS;
		}

		var target_url = data.host;
		if (data.hasDetails) {
			target_url += '/coverage/apps/' + data.project + '/' + data.branch;
		} else {
			target_url += '/apps/' + data.project;
		}

		// save coverage
		return gitlabHelper.updateCommitStatus({
			target_url: target_url,
			description: description,
			name: settings.gitlab.commitBuildStatusName,
			state: state,
			sha: data.commit,
			ref: data.branch,
			id: data.project
		}, data.token, data.tokenType)

		.then(function () {
			return q({
				project: data.project,
				branch: data.branch,
				commit: data.commit,
				coverage: data.coverage,
				diff: diff,
				status: state,
				hasDetails: data.hasDetails
			});
		});
	})

	// TODO: get commit info from gitlab and also save

	// save this commit
	.then(saveCommit)

	.catch(handleErrors.bind(null, data));
};

var getLastKnownCoverageOfBranch = function (projectId, branchName) {
	console.log('last known coverage');
	console.log(projectId);
	console.log(branchName);
	return App.getCoverageForBranch(projectId, branchName);
};

var compareCoverage = function (newCoverage, oldCoverage) {
	console.log('compare coverage');
	console.log(newCoverage);
	console.log(oldCoverage);
	var deferred = q.defer();
	deferred.resolve(newCoverage - oldCoverage);
	return deferred.promise;
};

var saveCommit = function (commit) {
	var deferred = q.defer();
	App.findOne({
		project_id: commit.project
	}, function (err, doc) {
		if (err) {
			return deferred.reject(err);
		}

		// TODO: currently default branch is always master, but need to support others
		if (commit.branch === 'master') {
			doc.coverage = commit.coverage;
		}

		commit.created_at = new Date();
		doc.commits = doc.commits || {};
		doc.commits[commit.commit] = commit;
		doc.markModified('commits');
		doc.save(function (err) {
			if (err) { console.error(err); }
		});
	});
	return deferred.promise;
};

var handleErrors = function (data, err) {
	console.error(err);
	buildFailed(Object.assign(data, {
		description: 'Something went wrong while processing coverage'
	}));
};
