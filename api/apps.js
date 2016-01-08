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

router.post('/:id/coverage', function (req, res) {
    upload.fields([{name: 'lcov'}, {name: 'zip'}])(req, res, function (err) {

        if (err) {
            console.error(err);
            res.json(500, {error: err});
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
