angular.module('yapp')
  .controller('LoginCtrl', function($scope, Session, $location, AUTH_REDIRECT, AuthService, $rootScope, $state) {
  	
  	if(AuthService.isAuthenticated())
        $state.go('dashboard');

  	$scope.login = function(data, form) {
       if(form.$valid)
        {   
            AuthService.login(data).then(function(res){
                if(res.status == "Success")
                {
                    res.data['location1'] = res.data.location;
                    Session.create(res.data, true);
                    $rootScope.$broadcast('SESSIONCHANGE');
                    if(res.data.status == "1")
                    {
                        $rootScope.$broadcast("msg" ,{'type':'success','title':'Success','msg':'Logged in Successfully!!'});
                        $state.go('dashboard');
                    }
                    else
                    	$rootScope.$broadcast("msg" ,{'type':'error','title':'Error','msg':'Your accout is Inactive contact your admin'});
                }
                else
                {
                	$rootScope.$broadcast("msg" ,{'type':'error','title':'Error','msg':res.status});
                }
                    //$scope.$$childHead.msg = res.status;
            }); 
        }
    }
});