'use strict';

var gitlabHelper = require('./gitlab.js');
var settings = require('../settings.json');
var q = require('q');
var App = require('../models/app');

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

	// If my current coverage is less than previous branch, its a failure
	.then(compareCoverage.bind(null, data.coverage))

	// Send success status with target_url path to coverage
	.then(function (diff) {
		return gitlabHelper.updateCommitStatus(Object.assign({
			target_url: settings.comforter.host + '/projects/' + data.project + '/coverage',
			description: 'Coverage is increased by ' + diff + '%',
			name: settings.gitlab.commitBuildStatusName,
			state: gitlabHelper.states.SUCCESS
		}, data));
	})

	.catch(handleErrors);
};

var getLastKnownCoverageOfBranch = function (projectId, branchName) {

};
