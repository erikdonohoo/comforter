'use strict';

var express = require('express');
var logger = require('morgan');
var cookieParser = require('cookie-parser');
var bodyParser = require('body-parser');
var mongoose = require('mongoose');
var settings = require('./settings.json');
var mongo = settings.mongo;
var restler = require('restler');

var apps = require('./api/apps');

var app = express();

// uncomment after placing your favicon in /public
// app.use(favicon(path.join(__dirname, 'public', 'favicon.ico')));
app.use(logger('dev'));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({extended: false}));
app.use(cookieParser());

// development error handler
// will print stacktrace
if (app.get('env') === 'development') {
	console.log('in dev mode');
	app.use(express.static(__dirname + '/.generated'));
	app.use(express.static(__dirname + '/app'));
	app.use(function (err, req, res, next) {
		res.status(err.status || 500);
		console.error(err.stack);
		res.json({error: 'oops'});
	});
} else {
	app.use(express.static(__dirname + '/dist'));
	// set url for prod
	app.use(function (req, res, next) {
		req.server_url = 'https://comforter.goreact.com';
		next();
	});
}

// serve up coverage files
app.use(express.static(__dirname + '/app-coverage-data'));

app.use('/api/apps', apps);

// production error handler
// no stacktraces leaked to user
app.use(function (err, req, res, next) {
	console.error(err.stack);
	res.status(err.status || 500);
	res.send(err);
});

// Oauth handling
app.get('/oauth/token', function (req, res) {
	var url = settings.gitlab.host + '/oauth/token?';
	url += 'client_id=' + req.query.client_id;
	url += '&client_secret=' + settings.gitlab.secret;
	url += '&code=' + req.query.code;
	url += '&grant_type=' + req.query.grant_type;
	url += '&redirect_uri=' + req.query.redirect_uri;

	restler.post(url, {data: {}})

	.on('success', function (token) {
		token.expires_in = token.expires_in || 3600;
		res.status(200).json(token);
	})

	.on('error', function (err) {
		res.status(401).json({error: err});
	})
	.on('fail', function (err) {
		res.status(401).json({error: err});
	});
});

if (app.get('env') === 'development') {
	app.get('/*', function (req, res) {
		// Just send the index.html for other files to support HTML5Mode
		res.sendFile(__dirname + '/app/index.html');
	});
} else {
	app.get('/*', function (req, res) {
		// Just send the index.html for other files to support HTML5Mode
		res.sendFile(__dirname + '/dist/index.html');
	});
}

// connect to mongodb
var connectString = 'mongodb://' + mongo.username + ':' + mongo.password + '@' +
	mongo.connection.host + ':' + mongo.connection.port + '/' + mongo.database;

mongoose.connect(connectString);

module.exports = app;
