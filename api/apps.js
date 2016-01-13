'use strict';

var express = require('express');
var router = express.Router();
var App = require('../models/app');
var multer = require('multer');
var crypto = require('crypto');
var path = require('path');
var q = require('q');
var build = require('../lib/build.js');

var storage = multer.diskStorage({
	destination: function (req, file, cb) {
		cb(null, './uploads/');
	},
	filename: function (req, file, cb) {
		crypto.pseudoRandomBytes(16, function (err, raw) {
			cb(null, raw.toString('hex') + '-' + Date.now() + '.' + path.extname(file.originalname));
		});
	}
});
var upload = multer({
	storage: storage
});

var parse = require('lcov-parse');
var math = require('mathjs');

/* GET users listing. */
router.get('/', function (req, res) {
	App.find({}).then(function (apps) {
		res.json(apps);
	});
});

// 1. Accept coverage % or LCOV along with optional zip
// 2. Validate params (branch, project, commit, apiKey) along with LCOV or %
// - Respond back to waiting process and move on with task
router.post('/:id/coverage', function (req, res) {
	upload.fields([{name: 'lcov'}, {name: 'zip'}])(req, res, function (err) {

		if (err) {
			console.error(err);
			return res.json(500, {
				error: err
			});
		}

		// check for required params
		if (!req.body.branch || !req.body.commit || !req.body.apiKey || !req.body.project) {
			return res.json(400, {error: 'missing required fields (branch, commit, apiKey, project)'});
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

			res.json({coverage: coverage});

			build({
				project: req.body.project,
				commit: req.body.commit,
				branch: req.body.branch,
				apiKey: req.body.apiKey,
				coverage: coverage
			});

			// move zip (if here) to location after unzipping

		}).catch(function (err) {
			console.error(err);
			res.json(400, {error: err});
		});

	});
});

router.post('/', function (req, res) {
	var newApp = new App(req.body);
	newApp.save(function (err) {
		if (err) throw err;
		res.json(req.body);
	});
});

function coverageFromLcov(data) {
	var totalFound = 0,
		totalCovered = 0;
	data.forEach(function (file) {
		for (var i in file) {
			var prop = file[i];
			if (!!prop && (prop.constructor === Object) && prop.hit) {
				totalFound += prop.found;
				totalCovered += prop.hit;
			}
		}
	});

	return math.round((totalCovered / totalFound) * 100, 4);
}

module.exports = router;
