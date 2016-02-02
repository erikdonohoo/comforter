'use strict';

var express = require('express');
var router = express.Router();
var App = require('../models/app');
var gitlab = require('../lib/gitlab');
var multer = require('multer');
var crypto = require('crypto');
var path = require('path');
var q = require('q');
var build = require('../lib/build.js');
var gitlabAuth = require('../lib/gitlab').gitlabAuth;
var fs = require('fs');
var targz = require('tar.gz');
var rimraf = require('rimraf');

var storage = multer.diskStorage({
	destination: function (req, file, cb) {
		cb(null, './uploads/');
	},
	filename: function (req, file, cb) {
		crypto.pseudoRandomBytes(16, function (err, raw) {
			cb(null, raw.toString('hex') + '-' + Date.now() + path.extname(file.originalname));
		});
	}
});
var upload = multer({
	storage: storage
});

var parse = require('lcov-parse');
var math = require('mathjs');

/* GET apps listing. */
router.get('/', gitlabAuth, function (req, res) {
	App.find({}).then(function (apps) {
		apps = apps.map(reduceCommits(5));
		res.status(200).json(apps);
	});
});

router.get('/:id', gitlabAuth, function (req, res) {
	App.findOne({project_id: req.params.id}, function (err, app) {
		if (err) { return res.status(500).json(err); }
		res.status(200).json(reduceCommits(100)(app));
	});
});

// 1. Accept coverage % or LCOV along with optional zip
// 2. Validate params (branch, project, commit, apiKey) along with LCOV or %
// - Respond back to waiting process and move on with task
router.post('/:id/coverage', gitlabAuth, function (req, res) {
	upload.fields([{name: 'lcov'}, {name: 'zip'}])(req, res, function (err) {

		if (err) {
			console.error(err);
			return res.status(500).json({
				error: err
			});
		}

		// check for required params
		if (!req.body.branch || !req.body.commit || !req.body.project) {
			return res.status(400).json({error: 'missing required fields (branch, commit, project)'});
		}

		var deferred = q.defer();

		if (req.files && req.files.lcov) {
			var lcov = req.files.lcov[0];
			parse(lcov.path, function (err, data) {
				deferred.resolve(coverageFromLcov(data));
			});
		} else if (req.body.coverage) {
			deferred.resolve(req.body.coverage);
		} else {
			deferred.reject('missing lcov or coverage information');
		}

		// TODO: Verify the apiKey will let me communicate with the GitLab API?

		deferred.promise.then(function (coverage) {

			res.status(200).json({coverage: coverage});

			addProject(req).then(function () {
				build({
					token: req.auth_token,
					tokenType: req.token_type,
					project: req.body.project,
					commit: req.body.commit,
					branch: req.body.branch,
					coverage: coverage,
					hasDetails: req.files.zip != null && req.files.zip.length,
					host: req.server_url || ('http://' + req.headers.host)
				});
			});

			// delete lcov and zip
			if (req.files.zip && req.files.zip.length) {
				// move zip (if here) to location after unzipping and then remove

				prepareDirectoryForZip(req.body.project, req.body.branch.replace(/\//g, '-'));

				var target = './app-coverage-data/coverage/apps/' + req.body.project;

				targz().extract(req.files.zip[0].path, target, function (err) {
					if (err) { console.error(err); }
					fs.unlink(req.files.zip[0].path);
				});
			}

			fs.unlink(req.files.lcov[0].path);

		}).catch(function (err) {
			if (err) { console.error(err); }
			res.status(400).json({error: err});
		});

	});
});

router.post('/', gitlabAuth, function (req, res) {
	var newApp = new App(req.body);
	newApp.save(function (err) {
		if (err) throw err;
		res.status(200).json(req.body);
	});
});

function folderExists (filePath) {
	try {
		return fs.statSync(filePath).isDirectory();
	}
	catch (err) {
		return false;
	}
}

function prepareDirectoryForZip (projectId, branch) {
	// make app-coverage-data and other folders if it doesn't exist
	if (!folderExists('./app-coverage-data')) {
		fs.mkdirSync('./app-coverage-data');
	}
	if (!folderExists('./app-coverage-data/coverage')) {
		fs.mkdirSync('./app-coverage-data/coverage');
	}
	if (!folderExists('./app-coverage-data/coverage/apps')) {
		fs.mkdirSync('./app-coverage-data/coverage/apps');
	}
	if (!folderExists('./app-coverage-data/coverage/apps/' + projectId)) {
		fs.mkdirSync('./app-coverage-data/coverage/apps/' + projectId);
	}
	if (folderExists('./app-coverage-data/coverage/apps/' + projectId + '/' + branch)) {
		rimraf.sync('./app-coverage-data/coverage/apps/' + projectId + '/' + branch);
	}
}

function addProject (request) {
	// check for project and add if necessary
	var deferred = q.defer();
	App.findOne({
		project_id: request.body.project
	}, function (err, app) {
		if (err) {
			return deferred.reject(err);
		}
		if (!app || !app.project_id) {

			gitlab.getProject(request.body.project, request.auth_token, request.token_type)
			.then(function (project) {
				var newApp = new App({
					project_id: project.id,
					name: project.name,
					coverage: 0
				});
				newApp.save(function (err) {
					if (err) {
						deferred.reject(err);
					}
					deferred.resolve();
				});
			}).catch(function (err) {
				deferred.reject(err);
			});

		} else {
			deferred.resolve();
		}
	});
	return deferred.promise;
}

function coverageFromLcov(data) {
	var totalFound = 0,
		totalCovered = 0;
	data.forEach(function (file) {
		for (var i in file) {
			var prop = file[i];
			if (!!prop && (prop.constructor === Object) && prop.found) {
				totalFound += prop.found;
				totalCovered += prop.hit;
			}
		}
	});

	return math.round((totalCovered / totalFound) * 100, 4);
}

var reduceCommits = function (commitCount) {
	return function (app) {
		var commits = app.commits || {};
		var list = Object.keys(commits).map(function (key) {
			return commits[key];
		}).sort(function (a, b) {
			return a.created_at > b.created_at ? -1 : 1;
		});
		list = list.slice(0, commitCount);
		commits = {};
		list.forEach(function (commit) {
			commits[commit.commit] = commit;
		});
		app.commits = commits;
		return app;
	};
};

module.exports = router;
