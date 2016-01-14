'use strict';

describe('apps directive', function () {
	beforeEach(module('comforter.apps'));

	var $compile, $scope;
	beforeEach(inject(['$compile', '$rootScope',
		function ($comp, $root) {
			$compile = $comp;
			$scope = $root.$new();
		}
	]));

	it('should make show content', function () {
		var el = $compile('<apps></apps>')($scope);
		$scope.$digest();
		expect(angular.element(el[0].querySelector('div')).text()).toBe('This is the apps directive');
	});
});
