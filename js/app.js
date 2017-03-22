'use strict';

/**
 * @ngdoc overview
 * @name yapp
 * @description
 * # yapp
 *
 * Main module of the application.
 */

var states = [
        { name: 'base', state: { abstract: true, url: '', templateUrl: 'views/base.html', data: {text: "Base", visible: false, auth:false } } },
        { name: 'login', state: { url: '/login', parent: 'base', templateUrl: 'views/login.html', controller:'LoginCtrl', data: {text: "Login", visible: false, auth:false } } },
        { name: 'forgotpassword', state: { url: '/forgotpassword', parent: 'base', templateUrl: 'views/forgotpassword.html', data: {text: "Forgot Password", visible: true , auth:false} } },
        { name: 'dashboard', state: { url: '/dashboard', parent: 'base', templateUrl: 'views/dashboard.html', controller:'DashboardCtrl', data: {text: "Dashboard", visible: false, auth:true } } },
        { name: 'order', state: { url: '/order', parent: 'base', templateUrl: 'views/order.html', controller:'OrderCtrl', data: {text: "Order", visible: false, auth:true } } },
        { name: 'new_order', state: { url: '/new_order', parent: 'base', templateUrl: 'views/new_order.html', controller:'NewOrderCtrl', data: {text: "Create Order", visible: false, auth:true } } },
        { name: 'user', state: { url: '/user', parent: 'base', templateUrl: 'views/user.html', controller:'UserCtrl', data: {text: "User", visible: false, auth:true } } },
        { name: 'product', state: { url: '/product', parent: 'base', templateUrl: 'views/product.html', controller:'ProductCtrl', data: {text: "Product", visible: false, auth:true } } },
        { name: 'logout', state: { url: '/login', data: {text: "Logout", visible: false }} }
    ];
   
angular.module('yapp', [
                'ui.router',
                'ngAnimate',
                'ui.rCalendar',
		        'toaster'
            ])
        .config(function($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.when('/', '/login');
            $urlRouterProvider.otherwise('/login');
            
            angular.forEach(states, function (state) {
                $stateProvider.state(state.name, state.state);
            });
            
            
        })

.factory('Cookies', function ($http) {
    var cookies = {};
             
    cookies.put = function (cname, cvalue) {
        var d = new Date();
        d.setTime(d.getTime() + (365*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    };
             
    cookies.get = function (cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    };
             
    return cookies;
})
.service('Session', function (Cookies) {
    this.create = function (user, save) {
        this.userId = user.id;
        this.user = user;
        if(save)
        Cookies.put("auth", JSON.stringify(user));
    };
    this.destroy = function () {
        this.userId = null;
        this.user = null;
        Cookies.put("auth", "");
    };
    this.setcurrency = function (currency) {
        Cookies.put("currency", currency);
    };
    this.cart_details = [];
})
.constant('AUTH_REDIRECT', {
    loginredirect: '/dashboard/',
    logoutredirect: '/'
})
.run(function($rootScope, $location, AuthService, Session, AUTH_REDIRECT, Cookies, $state, $timeout, $http, toaster) {
    if(!!Cookies.get("auth"))
        Session.create(JSON.parse(Cookies.get("auth")), false);

    $rootScope.$on("$stateChangeStart", function(args, start){
        console.log(start.data.text);
        $("title").text(start.data.text+" - Purple");
        if(start.data.auth && !AuthService.isAuthenticated())
        {
                $location.path('#login');
        }
        $('body').addClass('loading');
    });

     $rootScope.$on("$stateChangeSuccess", function(args, start){
        $timeout(function(){
             $('body').removeClass('loading');
        }, 1000);
      
    });

     $rootScope.$on('msg',function(msg, args) { 
    
      toaster.pop(args.type, args.title, args.msg);
      
     });

})
.filter('to_trusted', ['$sce', function($sce){
        return function(text) {
            return $sce.trustAsHtml(text);
        };
    }]);
