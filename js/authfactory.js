angular.module('yapp')

.factory('AuthService', function ($http, Session) {
    var authService = {};
    var api_url = '';

    authService.login = function (data) {
    return $http
        .post('api.php?action=login', data, {headers:{'Content-Type': 'application/x-www-form-urlencoded'}})
        .then(function (res) {
            return res['data'];
        }, function(res){
            return res;
        });
    };

    authService.create_user = function (data) {
    return $http
        .post('api.php?action=signup', data, {headers:{'Content-Type': 'application/x-www-form-urlencoded'}})
        .then(function (res) {
            return res['data'];
        });
    };

    authService.users = function (data) {
    return $http
        .get('api.php?action=users')
        .then(function (res) {
            return res['data'];
        });
    };

    authService.products = function (data) {
    return $http
        .get('api.php?action=products')
        .then(function (res) {
            return res['data'];
        });
    };

    authService.create_product = function (data) {
    return $http
        .post('api.php?action=add_product', data, {headers:{'Content-Type': 'application/x-www-form-urlencoded'}})
        .then(function (res) {
            return res['data'];
        });
    };
    

    authService.isAuthenticated = function () {
        return !!Session.userId;
    };
             
    return authService;
})