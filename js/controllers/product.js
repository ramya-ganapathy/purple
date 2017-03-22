'use strict';

/**
 * @ngdoc function
 * @name yapp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of yapp
 */
angular.module('yapp')
  .controller('ProductCtrl', function($scope, Session, $location, AUTH_REDIRECT, AuthService, $rootScope, $timeout, $state, toaster) {

  	$scope.user = Session.user;

  	$scope.ur = {status:1};
    $scope.products = [];

    AuthService.products().then(function(res){
      $scope.products = res['data'];
    });

    $scope.add_product = function()
    {
      if($scope.newform.$valid){
          AuthService.create_product(angular.copy($scope.ur)).then(function(){
            $scope.ur = {};
            $("#NewModal").modal("hide");

            AuthService.products().then(function(res){
              $scope.products = res['data'];
            });
          });
      }
    };

  	

  });
