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
		name: settings.gitlab.commitBuildStatusName + '/' + data.projectName,
		description: data.description || 'Uh oh, the coverage couldn\'t be handled'
	});
};

var makeTruthyPromise = function (value) {
	return value ? q.resolve(value) : q.reject(value);
}

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
		name: settings.gitlab.commitBuildStatusName + '/' + data.projectName,
		description: 'Comforter is handling the build'
	}, data.token, data.tokenType).catch(function () {
		buildFailed(data);
	})

	.then(function () {
		return makeTruthyPromise(data.mergeBase)
			.then(getLastKnownCommitInfo.bind(null, data.project, data.projectName))
		.catch(function () {
			return makeTruthyPromise(data.mergeRequestIID)
				.then(getMergeBaseFromMR.bind(null, data.project, data.token, data.tokenType))
				.then(getLastKnownCommitInfo.bind(null, data.project, data.projectName));
		})
		.catch(function () {
			return makeTruthyPromise(data.targetBranch)
				.catch(function () {
					return makeTruthyPromise(data.mergeRequestIID)
						.then(getTargetBranchFromMR.bind(null, data.project, data.token, data.tokenType));
				})
				.catch(gitlabHelper.getDefaultBranch.bind(gitlabHelper, data.project, data.token, data.tokenType))
				.catch(q.resolve('master'))
				.then(getLastKnownBranchInfo.bind(null, data.project, data.projectName));
		});
	})

	// get the coverage diff
	.then(function (oldInfo) {
		return compareCoverage(data, oldInfo);
	})

	// Send success status with target_url path to coverage
	.then(function (diff) {

		// coverage drop
		var description, state;
		if (diff.change < 0) {
			description = 'Coverage was decreased by ' + (diff.change * -1) + '%';

			if (diff.allowLower) {
				state = gitlabHelper.states.SUCCESS;
			} else {
				state = gitlabHelper.states.FAILED;
			}

		} else {
			description = 'Coverage is increased by ' + diff.change + '%';
			state = gitlabHelper.states.SUCCESS;
		}

		var target_url = data.host + '/apps/' + data.project + '/' + data.projectName + '?commit=' + data.commit;

		// save coverage
		return gitlabHelper.updateCommitStatus({
			target_url: target_url,
			description: description,
			name: settings.gitlab.commitBuildStatusName + '/' + data.projectName,
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
				projectName: data.projectName,
				diff: diff,
				status: state,
				hasDetails: data.hasDetails
			});
		});
	})

	// TODO: get commit info from gitlab and also save

	// save this commit
	.then(saveCommit.bind(null, data.token, data.tokenType))

	.catch(function (err) {
		handleErrors(data, err);
	});
};

var getLastKnownBranchInfo = function (projectId, projectName, branchName) {
	return App.getInfoForBranch(projectId, branchName, projectName);
};

var getLastKnownCommitInfo = function (projectId, projectName, commitHash) {
	return App.getInfoForCommit(projectId, commitHash, projectName);
};

var getMergeBaseFromMR = function (projectId, token, tokenType, mergeRequestIID) {
	return gitlabHelper.getMergeRequest(projectId, mergeRequestIID, token, tokenType)
		.then(function (mr) {
			return mr.diff_refs.base_sha;
		});
};

var getTargetBranchFromMR = function (projectId, token, tokenType, mergeRequestIID) {
	return gitlabHelper.getMergeRequest(projectId, mergeRequestIID, token, tokenType)
		.then(function (mr) {
			return mr.target_branch;
		});
};

var upgradeOldCoverageIfNeeded = function (coverage) {
	if (typeof coverage === 'number') {
		return {
			percent: coverage,
			totalLines: 0,
			totalCovered: 0
		}
	}
	return coverage;
};

var compareCoverage = function (newInfo, oldInfo) {
	oldInfo.coverage = upgradeOldCoverageIfNeeded(oldInfo.coverage);
	return {
		change: newInfo.coverage.percent - oldInfo.coverage.percent,
		comparedBranch: oldInfo.branch,
		comparedCommit: oldInfo.commit,
		allowLower:
			newInfo.coverage.totalLines < oldInfo.coverage.totalLines && (
			oldInfo.coverage.totalLines - oldInfo.coverage.totalCovered >=
			newInfo.coverage.totalLines - newInfo.coverage.totalCovered
		)
	};
};

var saveCommit = function (token, tokenType, commit) {
	var deferred = q.defer();
	App.findOne({
		project_id: commit.project,
		project_name: commit.projectName
	}, function (err, doc) {
		if (err) {
			return deferred.reject(err);
		}

		gitlabHelper.getDefaultBranch(doc.project_id, token, tokenType).then(function (defaultBranch) {
			if (commit.branch === defaultBranch) {
				doc.coverage = commit.coverage;
			}

			commit.created_at = new Date();
			doc.commits = doc.commits || {};
			doc.commits[commit.commit] = commit;
			doc.markModified('commits');
			doc.save(function (err) {
				if (err) {
					return deferred.reject(err);
				}
				deferred.resolve(doc);
			});
		}, deferred.reject.bind(deferred));
	});
	return deferred.promise;
};

var handleErrors = function (data, err) {
	console.error(err);
	buildFailed(Object.assign(data, {
		description: 'Something went wrong while processing coverage'
	}));
};
