'use strict';

/**
 * @ngdoc function
 * @name yapp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of yapp
 */
angular.module('yapp')
  .controller('UserCtrl', function($scope, Session, $location, AUTH_REDIRECT, AuthService, $rootScope, $timeout, $state, toaster) {

  	$scope.user = Session.user;
    $scope.ur = {status:1, userrole:"sales_person"};
    $scope.users = [];

    AuthService.users().then(function(res){
      $scope.users = res['data'];
    });

  	$scope.add_user = function()
  	{
  		if($scope.newform.$valid){
          AuthService.create_user(angular.copy($scope.ur)).then(function(){
            $scope.ur = {};
            $("#NewModal").modal("hide");

            AuthService.users().then(function(res){
              $scope.users = res['data'];
            });
          });
      }
  	};

  	

  });
