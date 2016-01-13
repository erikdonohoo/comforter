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
	}).catch(function () {
		buildFailed(data);
	});

	// Get default branch
	gitlabHelper.getDefaultBranch()

	// Get last known coverage of the branch
	.then(getLastKnownCoverageOfBranch.bind(null, data.project))

	// get the coverage diff
	.then(compareCoverage.bind(null, data.coverage))

	// Send success status with target_url path to coverage
	.then(function (diff) {

		// coverage drop
		var description, state;
		if (diff <= 0) {
			description = 'Coverage was decreased by ' + (diff * -1) + '%';
			state = gitlabHelper.states.FAILURE;
		} else {
			description = 'Coverage is increased by ' + diff + '%';
			state = gitlabHelper.states.SUCCESS;
		}

		// save coverage
		gitlabHelper.updateCommitStatus(Object.assign({
			target_url: settings.comforter.host + '/projects/' + data.project + '/coverage',
			description: description,
			name: settings.gitlab.commitBuildStatusName,
			state: state
		}, data));

		return q.when({project: data.project, branch: data.branch, commit: data.commit, coverage: data.coverage, diff: diff});
	})

	// save this commit
	.then(saveCommit)

	.catch(handleErrors.bind(null, data));
};

var getLastKnownCoverageOfBranch = function (projectId, branchName) {
	return App.getCoverageForBranch(projectId, branchName);
};

var compareCoverage = function (newCoverage, oldCoverage) {
	var deferred = q.defer();
	deferred.resolve(newCoverage - oldCoverage);
	return deferred.promise;
};

var saveCommit = function (commit) {
	var deferred = q.defer();
	App.findOne({project_id: commit.project}, function (err, doc) {
		if (err) { return deferred.reject(err); }
		commit.created_at = new Date();
		doc.commits[commit.commit] = commit;
		doc.save();
	});
	return deferred.promise;
};

var handleErrors = function (data, err) {
	console.error(err);
	buildFailed(Object.assign(data, {description: 'Something went wrong while processing coverage'}));
};
