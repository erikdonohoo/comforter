var express = require('express');
var router = express.Router();
var App = require('../models/app');

/* GET users listing. */
router.get('/', function(req, res, next) {
    App.find({}, function (err, apps) {
        if (err) throw err;

        res.json(apps);
    });
});

router.post('/', function (req, res, next) {
    var newApp = new App(req.body);
    newApp.save(function (err) {
        if (err) throw err;
        res.json(req.body);
    });
});

module.exports = router;
