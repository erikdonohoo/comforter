'use strict';

var mongoose = require('mongoose');
var Schema = mongoose.Schema;
var q = require('q');

// commits will look something like this
/*
	{
		commitSha: {coverage: Number, branch: String, created_at: Date},
		...
	}
*/

var appSchema = new Schema({
	name: String,
	created_at: Date,
	modified_at: Date,
	coverage: Number,
	project_id: String,
	commits: Schema.Types.Mixed
});

appSchema.pre('save', function (next) {

	if (!this.created_at) {
		this.created_at = new Date();
	}

	this.modified_at = new Date();

	next();
});

// Get coverage for a branch
appSchema.statics.getCoverageForBranch = function (projectId, branchName) {
	var deferred = q.defer();
	this.where('project_id', projectId).exec(function (err, apps) {
		if (err) { return deferred.reject(err); }
		if (!apps.length) { return deferred.resolve(0); }
		var matches = Object.keys(apps[0].commits).map(function (commit) {
			return apps[0].commits[commit];
		}).filter(function (commit) {
			return commit.branch === branchName;
		}).sort(function (first, second) {
			return first.created_at < second.created_at ? -1 : 1;
		});
		if (!matches.length) { return deferred.resolve(0); }
		return deferred.resolve(matches[0].coverage);
	});
	return deferred.promise;
};

var App = mongoose.model('App', appSchema);

module.exports = App;
