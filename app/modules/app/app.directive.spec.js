'use strict';

describe('app directive', function () {
	beforeEach(module('comforter.app'));

	var $compile, $scope;
	beforeEach(inject(['$compile', '$rootScope',
		function ($comp, $root) {
			$compile = $comp;
			$scope = $root.$new();
		}
	]));

	it('should make show content', function () {
		var el = $compile('<app></app>')($scope);
		$scope.$digest();
		expect(angular.element(el[0].querySelector('div')).text()).toBe('This is the app directive');
	});
});
