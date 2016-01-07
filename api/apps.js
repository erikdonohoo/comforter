'use strict';

var express = require('express');
var router = express.Router();
var App = require('../models/app');
var multer = require('multer');
var storage = multer.diskStorage({});
var limits = {fileSize: 10 * 1024 * 1024};
var upload = multer({storage: storage, limits: limits});
var parse = require('lcov-parse');
var math = require('mathjs');

/* GET users listing. */
router.get('/', function (req, res) {
    App.find({}).then(function (apps) {
        res.json(apps);
    });
});

router.post('/:id/coverage', function (req, res) {
    parse('./lcov.info', function (err, data) {
        res.json({coverage: coverageFromData(data)});
    });
});

router.post('/', function (req, res) {
    var newApp = new App(req.body);
    newApp.save(function (err) {
        if (err) throw err;
        res.json(req.body);
    });
});

function coverageFromData (data) {
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
