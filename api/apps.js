'use strict';

var express = require('express');
var router = express.Router();
var App = require('../models/app');
var multer = require('multer');
var upload = multer({dest: './uploads'});
var parse = require('lcov-parse');
var math = require('mathjs');

/* GET users listing. */
router.get('/', function (req, res) {
    App.find({}).then(function (apps) {
        res.json(apps);
    });
});

// 1. Accept coverage % or LCOV along with optional zip
// 2. Validate params (branch, project, commit) along with LCOV or %
// - Respond back to waiting process and move on with task
// 3. Tell GitLab via commit status a job has begun
// - If any step after 3, but before X fails, update commit status with failure
// 4. Get coverage %
// 5. Determine via GitLab API which branch of this project is default
// 6. Get last known coverage for that branch in this project
// 7. If this coverage is less than last known, it is a failure.
// 8. Report discrepancy on commit status fail with target_url to the zip of coverage (if included)
// 9. Otherwise pass and update commit status with success and target_url to zip (if included)
router.post('/:id/coverage', function (req, res) {
    upload.fields([{name: 'lcov'}, {name: 'zip'}])(req, res, function (err) {

        if (err) {
            console.error(err);
            return res.json(500, {error: err});
        }

        if (req.files && req.files.lcov) {
            var lcov = req.files.lcov[0];
            parse(lcov.path, function (err, data) {
                res.json({coverage: coverageFromLcov(data)});
            });
        } else if (req.body.coverage) {
            res.json({coverage: req.body.coverage});
        } else {
            res.json(400, {error: 'missing lcov or coverage information'});
        }
    });
});

router.post('/', function (req, res) {
    var newApp = new App(req.body);
    newApp.save(function (err) {
        if (err) throw err;
        res.json(req.body);
    });
});

function coverageFromLcov (data) {
    var totalFound = 0, totalCovered = 0;
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
