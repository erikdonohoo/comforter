'use strict';

var mongoose = require('mongoose');
var Schema = mongoose.Schema;

// commits will look something like this
/*
	{
		commitSha: {coverage: Number, branch: String},
		...
	}
*/

var appSchema = new Schema({
	name: String,
	created_at: Date,
	modified_at: Date,
	coverage: Number,
	commits: Schema.Types.Mixed
});

appSchema.pre('save', function (next) {

	if (!this.created_at) {
		this.created_at = new Date();
	}

	this.modified_at = new Date();

	next();
});

var App = mongoose.model('App', appSchema);

module.exports = App;
