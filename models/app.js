'use strict';

var mongoose = require('mongoose');
var Schema = mongoose.Schema;

var appSchema = new Schema({
	name: String,
	created_at: Date
});

appSchema.pre('save', function (next) {

	if (!this.created_at) {
		this.created_at = new Date();
	}

	next();
});

var App = mongoose.model('App', appSchema);

module.exports = App;
