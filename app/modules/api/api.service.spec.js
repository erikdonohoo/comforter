'use strict';

describe('api service', function () {
	beforeEach(module('comforter.api'));

	var apiService;
	beforeEach(inject(['apiService',
		function (service) {
			apiService = service;
		}
	]));

	it('should get 1', function () {
		expect(apiService.doThing()).toBe(1);
	});
});
