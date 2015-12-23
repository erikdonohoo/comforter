'use strict';

describe('apps route', function () {
	beforeEach(function () {
		browser.get('/apps');
	});
	it('should be on apps', function () {
		browser.getCurrentUrl().then(function (url) {
			expect(url).toContain('#/apps');
		});
	});
});
