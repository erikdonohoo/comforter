'use strict';

describe('home route', function () {
	beforeEach(function () {
		browser.get('/');
	});
	it('should be on home', function () {
		browser.getCurrentUrl().then(function (url) {
			expect(url).toContain('#/');
		});
	});
});
