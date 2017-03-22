'use strict';

/**
 * @ngdoc function
 * @name yapp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of yapp
 */
angular.module('yapp')
  .controller('OrderCtrl', function($scope, Session, $location, AUTH_REDIRECT, AuthService, $rootScope, $timeout, $state, toaster) {

  	$scope.user = Session.user;

  	$scope.logout = function()
  	{
  		Session.destroy();
  		$scope.session_user = Session.user;
		  $scope.is_user_logged_in = AuthService.isAuthenticated();
      $location.path('#login');
  	};

  	

  });
