'use strict';

/**
 * @ngdoc function
 * @name yapp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of yapp
 */
angular.module('yapp').controller('VenuesCtrl', function($scope,$http,$timeout, $state, AuthService, $location, Session, $rootScope, $sce,ezfb) {
    
    if(typeof $rootScope.search != "undefined")
    {
        $scope.search = $rootScope.search;
        delete $rootScope.search;
    }
    

    $scope.venues = [];
    //$scope.user_details = [];
    AuthService.venues().then(function(res){
      if(res['status'] == 'Success')
      {
          $scope.venues = res['data'];
          $scope.actions();
      }
    });
     
    $scope.user = {};
    if($state.current.name == 'signup')
    $scope.user.user_role = 'user';
    else 
    $scope.user.user_role = 'owner';
    
    $rootScope.$on('$stateChangeStart', 
    function(event, toState, toParams, fromState, fromParams){ 
    
    $scope.allmapshow = false;
    
    if(toState.name == 'signup')
        $scope.user.user_role = 'user';
    else 
        $scope.user.user_role = 'owner';
    });
    
     $http.get("country_code.json").then(function (res) {

       $scope.allCountryWithCode = res['data'];
     });
    
    $scope.signup = function(user, form){
        if(form.$valid){
            AuthService.signup(user).then(function(res){
                if(res.status == "Success")
                {
                    $rootScope.$broadcast("msg" ,{'type':'success','title':'Success','msg':'Registration Successfully completed!!'});
                    $scope.login(user, form);
                }
                else
                {
                	$rootScope.$broadcast("msg" ,{'type':'error','title':'Error','msg':res.status});
                }
                    //$scope.$$childHead.msg = res.status;
            });
        }
    };

    $scope.login = function(data, form) {
    console.log(data);
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
                        if($rootScope.redirectTo === undefined)
                        {
                            $location.path('#/home');
                        }
                        else
                        {
                            $rootScope.redirectTo = '';
                            $state.go('order');
                        }
                    }
                    else
                        $state.go('verify_mobile');
                }
                else
                {
                	$rootScope.$broadcast("msg" ,{'type':'error','title':'Error','msg':res.status});
                }
                    //$scope.$$childHead.msg = res.status;
            }); 
        }
    }
    
    $scope.facebookLogin = function (usertype) {
        
        ezfb.login(function (res) {
            
          if (res.authResponse) {
             ezfb.getLoginStatus(function (res) {
                  if(res.status == 'connected'){
                     ezfb.api('/me', function (res){  
                                            
                    var usr ={account_number:null,address:"",city:"",email:"",id:"facebook",last_name:"",location:"",location1:"",mobile:"",mobile_country_code:"",
                        name:res.name,new_password:"",password:"",postcode:"0",state:"",status:"1",user_role:usertype};
                        console.log(usr);
                         Session.create(usr, true);
                         $rootScope.$broadcast('SESSIONCHANGE');
                         $rootScope.$broadcast("msg" ,{'type':'success','title':'Success','msg':'Logged in Successfully!!'});
                         $location.path('/myprofile');
                    });
                  }
             });
          }
        }, {scope: 'email,user_likes'});
     };
    
    
    $scope.resetpassword = function(data,form){
        if(form.$valid)
        {   
            AuthService.checkmobile(data).then(function(res){
                if(res.code == 200)
                {
                    var ctr_code = res.data.user.mobile_country_code.split('');
            if(ctr_code[0] == '+' || ctr_code[0] == '0'){ ctr_code.splice(0,1); }
            var ctrycode = ctr_code.join('');
            
                    AuthService.send_otp({"countryCode": ctrycode, "mobileNumber": data.mobile}).then(function(res1){
                        $scope.$$childTail.otp = res1.otp;
                        $scope.otp = res1.otp;
                        $rootScope.verify_user = res.data.user.id;
                        console.clear();
                    });

                    
                }
                else
                     $rootScope.$broadcast("msg" ,{'type':'error','title':'Error','msg':'Mobile Number not exist!!'});
            }); 
        }
    };

    $scope.update_profile = function(user, form){
        if(form.$valid){
            //console.log("valid");
            AuthService.update_profile(user).then(function(res){
                if(res['status'] != "error")
                {
                    //Session.destroy(user);
                   // Session.create(res['data'], true);
                   // $rootScope.$broadcast('SESSIONCHANGE');
                   // $scope.user_details = res['data'];
                    //console.log(res['data']);

                     $rootScope.$broadcast("msg" ,{'type':'success','title':'Success','msg':'Password updated Successfully!!'});
                     $location.path('/venues/login');
                }
            });
        }
    };
  
    $scope.get_map_link = function(){
        var $url = '';
        angular.forEach($scope.venues, function(v,k){
            $url += $url == '' ? "q[]="+v.venue_name+", "+v.address_location : "&q[]="+v.venue_name+", "+v.address_location;
        });
        return "map.php?"+$url;
    };

    var month_name=new Array(12);
    month_name[0]="January";
    month_name[1]="February";
    month_name[2]="March";
    month_name[3]="April";
    month_name[4]="May";
    month_name[5]="June";
    month_name[6]="July";
    month_name[7]="August";
    month_name[8]="September";
    month_name[9]="October";
    month_name[10]="November";
    month_name[11]="December";

    $scope.page_data = {};
    $scope.week_days_available = [];
    $scope.months_available = [];
    $scope.week_booking = {};

    $(document).on('click', ".left", function(){
        $("#myCarousel").carousel("prev");
      //  console.log("dfsd");
    });
    $(document).on('click', ".right", function(){
        $("#myCarousel").carousel("next");
    });
 //console.log($scope.active_state);
    $scope.actions = function(){
        if($scope.active_state == 'detail' || $scope.active_state == 'map')
        {
            $http.get('http://zenstill.com/demo/findavenue/api.php?action=add_viewed&venue_id='+$scope.active_id).then(function(res){
        });
            AuthService.venue_detail($scope.active_id, new Date().getMonth()+1).then(function(res){
                if(res['status'] == 'Success')
                {
                    $scope.page_data = res['data'];
                    $scope.week_days_available = res['data']['week_days_available'];
                    var sumweekdays = $scope.week_days_available.reduce(function(a,b){return a+b;}, 0);
                    $scope.week_days_available.unshift(sumweekdays);
                    $scope.months_available = res['data']['months_available'];
                    $scope.page_data.venue_description = $sce.trustAsHtml($scope.page_data.venue_description);

                    $scope.active_tab_id = 0;
                    $scope.active_tab_image = $scope.page_data.images;
                    $scope.map_url = '<iframe src="https://maps.google.com/maps?&q='+$scope.page_data.address_street+','+$scope.page_data.address_county+','+$scope.page_data.address_town+','+$scope.page_data.address_city+','+$scope.page_data.address_country+'&output=embed" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>';
              
                    $timeout(function(){
                        angular.element('#myCarousel').carousel();
                    }, 1000);

                    $scope.map_url = '<iframe src="https://maps.google.com/maps?&q='+$scope.page_data.address_street+','+$scope.page_data.address_county+','+$scope.page_data.address_town+','+$scope.page_data.address_city+','+$scope.page_data.address_country+'&output=embed" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>';
                    $(".ng-scope").scrollTop( 10 );
                }
            });
        }
        else if($scope.active_state == 'book')
        {
            $http.get('http://zenstill.com/demo/findavenue/api.php?action=add_viewed&venue_id='+$scope.active_id).then(function(res){
        });
            var curr = new Date(); 
            var first = curr.getDate() - curr.getDay();

            $scope.get_booking_detail(curr, first);
        }
        else if($scope.active_state == 'order')
        {
            $scope.order_details = Session.cart_details;
            $scope.is_user_logged_in = AuthService.isAuthenticated();
        }
        else if($scope.active_state == 'login' || $scope.active_state == 'signup')
        {
            if(AuthService.isAuthenticated())
                $location.path('/venues');
        }
        else if($scope.active_state == 'verify_mobile')
        {
            if(AuthService.isAuthenticated())
                $location.path('/venues');
        }
        else if($scope.active_state == 'order_success')
        {
            
            $scope.order_details = Session.cart_details;
            var data = {user_id: Session.userId, total_amt: $scope.total_amount_wit_tax(), booking_details: $scope.cart_details, currency: $scope.exratecurrency};

            AuthService.booking(data).then(function(res){
                Session.cart_details = [];
                $rootScope.$broadcast("CARTCHANGE");
                localStorage.setItem('cart_details', JSON.stringify(Session.cart_details));
                $timeout(function(){
                    //$state.go("myprofile");
                    window.location.assign(window.location.href.split("?")[0]+"#myprofile");
                }, 3000);
            });
        }
        else if($scope.active_state == 'order_cancel')
        {
            $timeout(function(){
                //$state.go("myprofile");
                window.location.assign(window.location.href.split("?")[0]+"#home");
            }, 3000);
        }
    };

    $scope.clear_cart = function(){
        Session.cart_details = [];
        $scope.order_details = [];
        $rootScope.$broadcast("CARTCHANGE");
        localStorage.setItem('cart_details', JSON.stringify(Session.cart_details));
    };

    $scope.delete_cart_item = function(ind){
        if($scope.order_details[ind].facility_id != '0')
        {
            $scope.order_details.splice(ind, 1);
        }
        else
        {
            var venue_id = $scope.order_details[ind].venue_id;
            var facility_id = $scope.order_details[ind].facility_id;
            var booking_time = $scope.order_details[ind].booking_time;
            var splice_cnt = 1;
            for(var $i=ind+1;$i<$scope.order_details.length;$i++)
            {
                if($scope.order_details[$i].venue_id == venue_id && $scope.order_details[$i].booking_time == booking_time)
                {
                    splice_cnt++;
                }
                else
                    break;
            }
            $scope.order_details.splice(ind, splice_cnt);
        }

        Session.cart_details = $scope.order_details;
        $rootScope.$broadcast("CARTCHANGE");
        localStorage.setItem('cart_details', JSON.stringify(Session.cart_details));
    };

    $scope.place_order = function()
    {
    
        if(!AuthService.isAuthenticated())
        {
            $rootScope.redirectTo = 'order';
            $state.go('login');
            return;
        }
        
       /*
       // var tot_amt = $scope.exratecurrency ? $rootscope.exrate[$scope.exratecurrency][$scope.exratecurrency] * $scope.total_amount() : $scope.total_amount();
        var data = {user_id: Session.user_id, total_amount: $scope.total_amount_wit_tax(), booking_details: $scope.cart_details};
        var currency_code = $scope.exratecurrency == 'INR' ? 'USD' : $scope.exratecurrency;
        var nname = $scope.exratecurrency == 'INR' ? 'FindaSportvenue(INR '+$scope.total_amount_wit_tax()+')' : 'FindaSportvenue';
        var $paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_xclick&no_note=1&lc=UK&currency_code="+currency_code+"&bn=PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest";
        $paypal += "&first_name=vino&last_name=gautam&payer_email=dhanavel237vino@gmail.com&item_number=1";

        $paypal += "&business=vinodhagan.samsys@gmail.com&item_name="+nname+"&amount="+$scope.total_amount_inr();
        $paypal += "&return="+window.location.origin+window.location.pathname+"?success";
        $paypal += "&cancel_return="+window.location.origin+window.location.pathname+"?cancel";
        $paypal += "&notify_url="+window.location.origin+window.location.pathname+"#notify";
        */
        //console.log($paypal);
        /*AuthService.booking(data).then(function(res){
            Session.cart_details = [];
            $rootScope.$broadcast("CARTCHANGE");
            localStorage.setItem('cart_details', JSON.stringify(Session.cart_details));
            $state.go("venues");
        });*/
        //window.location.assign($paypal);
      
        var ttlamt = $scope.total_amount_inr();
        var cusid = 'CUST'+Session.user.id;
        var orderid = 'ORDS'+Math.floor((Math.random() * 1000) + 1) + Math.floor((Math.random() * 1000) + 1);
        var d = new Date().getTime();

        $('#CUST_ID').val(cusid);
        $('#ORDER_ID').val(orderid);
        $('#FSTXN_AMOUNT').val(1);
        $('#tid').val(d);
        $('#ccPayment').submit();

    };


    $scope.dfdate = function(db)
    {
        if(db === undefined) return;
        return db.toISOString().slice(0,10);
    };
    
    
    $scope.dfdateformat = function(db)
    {
        if(db === undefined || typeof db != "string") return;
        return db.split("-").reverse().join("-");
    };
    
    $scope.df2date = function(db)
    {
        if(db === undefined) return;

        var month_day = db.getDate();
        var Nth = "th";
        if(month_day === 1 || month_day === 21 || month_day === 31){
            Nth = "st";
        }
        else if(month_day === 2 || month_day === 22){
            Nth = "nd";
        }
        else if(month_day === 3 || month_day === 23){
            Nth = "rd";
        }

        return month_day+Nth+" "+month_name[db.getMonth()]+" "+db.getFullYear();
    };
    
    $scope.booking_date_filter_change = function(selected)
    {
        var curr = $scope.firstday;
        var selecteds = new Date(selected);
        var first = selecteds.getDate() - selecteds.getDay("monday");

        $scope.get_booking_detail(selecteds, first); 
    };

    $scope.previous_week = function()
    {
        var curr = $scope.firstday;
        var first = curr.getDate() - curr.getDay() - 7;

        $scope.get_booking_detail(curr, first); 
    };

    $scope.next_week = function()
    {
        var curr = $scope.lastday;
        var first = curr.getDate() - curr.getDay() + 7;

        $scope.get_booking_detail(curr, first);
    };

    $scope.get_booking_detail = function(curr, first)
    {
        var dddate = new Date(curr.setDate(first));

        $scope.week_booking = {};
        $scope.weeks = [];
        $scope.hours = [];
        for(var $i=0;$i<=6;$i++)
        {
            if($i!=0)
                dddate = new Date(curr.setDate(curr.getDate() + 1));

            $scope.weeks.push($scope.dfdate(dddate));
            if($i == 0)
            {
                $scope.firstday = dddate;
            }

            if($i == 6)
            {
                $scope.lastday = dddate;
            }

            $scope.week_booking[$scope.dfdate(dddate)] = {booked_status: 0};

            for(var $j=6;$j<23;$j++)
            {
                var hhours;
                if($j>9)
                    hhours = $j+':00:00';
                else
                    hhours = $j+':00:00';

                $scope.week_booking[$scope.dfdate(dddate)][hhours] = 0;

                if($i==0)
                {
                    if($j == 11)
                        $scope.hours.push({label: '11AM - 12PM', val: hhours});
                    else if($j == 23)
                        $scope.hours.push({label: '11PM - 12AM', val: hhours});
                    else if($j == 0)
                        $scope.hours.push({label: '12 - 1 AM', val: hhours});
                    else if($j == 12)
                        $scope.hours.push({label: '12 - 1 PM', val: hhours});
                    else if($j>11)
                        $scope.hours.push({label: ($j-12) +' - '+ ($j-12+1) + ' PM', val: hhours});
                    else
                        $scope.hours.push({label: $j+' - '+ ($j+1) + ' AM', val: hhours});
                }
            }
        }
        
        $scope.sports_array = [];

        $scope.eventSource = [];
        $scope.currentDate = new Date();

        AuthService.venue_detail($scope.active_id, $scope.firstday.getMonth()+1).then(function(res){
            if(res['status'] == 'Success')
            {
                $scope.page_data = res['data'];
                $scope.nearby = [];

                $http.get("http://findasportvenue.com/nearbyplaces.php?lat="+$scope.page_data.latitude+"&lng="+$scope.page_data.longitude).then(function(res11){
                    $scope.nearby = res11['data']['results'];
                });

                $scope.week_days_available = res['data']['week_days_available'];
                $scope.months_available = res['data']['months_available'];
                
                var sumweekdays = $scope.week_days_available.reduce(function(a,b){return parseInt(a)+parseInt(b);}, 0);
                $scope.week_days_available.unshift(sumweekdays);
                var sumweekdays = $scope.months_available.reduce(function(a,b){return parseInt(a)+parseInt(b);}, 0);
                $scope.months_available[0] = sumweekdays;

                angular.forEach($scope.page_data.sports, function(v1,k1){
                    if(k1 == 0)
                    $scope.booking_sport = v1.id;
                    $scope.sports_array[v1.id] = v1.sport_name;
                });

                $scope.monthlySource = $scope.monthlySourcefn();
            }
        });
        
        
        
        /*AuthService.booking_detail($scope.dfdate($scope.firstday), $scope.dfdate($scope.lastday), $scope.active_id).then(function(res){
            angular.forEach(res.data, function(v,k){
               if(k == 'blocked_dates')
               {
                    angular.forEach(v, function(v1,k1){
                       if(typeof $scope.week_booking[v1] != "undefined")
                            $scope.week_booking[v1]['booked_status'] = 4;
                    });
               }
               else
               {
                    if(v.booked_time.length)
                    {
                        angular.forEach(v.booked_time, function(v1,k1){
                            //console.log(v.booking_date, v1, $scope.week_booking[v.booking_date][v1]);
                            if(typeof $scope.week_booking[v.booking_date][v1] != "undefined")
                            {
                                $scope.week_booking[v.booking_date][v1] = 1;
                                $scope.week_booking[v.booking_date]['booked_status'] = 1;
                            }    
                        });
                    }
                    else
                    {
                        $scope.week_booking[v.booking_date]['booked_status'] = 5;
                    }
               }
            });
        });

        angular.forEach(Session.cart_details, function(v,k){
            $scope.week_booking[v.booking_date]['booked_status'] = 3;
            if(v.booked_time[0] != 'booked_status')
            {
                angular.forEach(v.booked_time, function(v1,k1){
                    if(typeof $scope.week_booking[v.booking_date][v1] != "undefined" && v.venue_id == $scope.active_id)
                    {    
                        $scope.week_booking[v.booking_date][v1] = 3;
                        $scope.week_booking[v.booking_date]['booked_status'] = 3;
                    }
                });
            }
            else
            {
                $scope.week_booking[v.booking_date]['booked_status'] = 6;
            }
        });

        AuthService.booking_detail($scope.dfdate($scope.firstday), $scope.dfdate($scope.lastday), $scope.active_id).then(function(res){
            angular.forEach(res.data, function(v,k){
                $scope.week_booking[v]['booked_status'] = 1;
                angular.forEach(v.blocked_dates, function(v1,k1){
                    if(typeof $scope.week_booking[v][v1] != "undefined")
                    {
                        $scope.week_booking[v][v1] = 1;
                        $scope.week_booking[v]['booked_status'] = 1;
                    }
                });
            });
        });*/

        $scope.show_calendar = false;
        $timeout(function(){
            $scope.show_calendar = true;
        }, 1000);

        $scope.start_time = 6;

        $scope.end_time = 10;

        $scope.show_stet = false;
        
        $scope.current_selection_date = false;
        
        $scope.sselected_slots = [];
        
        $scope.store_time_data = {};
        
        $scope.booked_slot = 0;
        
        $scope.sselected_slots = [];
        $scope.sbooked_slots = [];
        
        Session.cart_details = [];
        $rootScope.$broadcast("CARTCHANGE");
        localStorage.setItem('cart_details', JSON.stringify(Session.cart_details));
        
        $scope.bbooked_ddetails = [];
    };



    $scope.times1 = [6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21];

    $scope.times2 = [7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23];
    
    $scope.store_time_data = {};

    $scope.show_calendar = false;

    $scope.monthlySource = [];

    $scope.show_stet = false;
    
    $scope.current_selection_date = false;
    
    $scope.sselected_slots = [];
    $scope.sbooked_slots = [];
    $scope.sselected_slot = function(id){
        
        if($scope.sis_selected_slot(id) == 2)
        return;
        
        var indd = $scope.sselected_slots.indexOf(id);
        
        if(indd == -1)
            $scope.sselected_slots.push(id);
        else
            $scope.sselected_slots.splice(indd, 1);
    };
    
    $scope.sis_selected_slot = function(id){
        if($scope.sbooked_slots.indexOf(id) != -1) return 2;
        else if($scope.sselected_slots.indexOf(id) != -1) return 1;
        else return 0;
    };
    $scope.sreset = function()
    {
        $scope.current_selection_date = false;
    
        $scope.sselected_slots = [];
    };
    
    $scope.onTimeSelected = function (selectedTime) {
        
        var sport_type = $scope.booking_sport;
    	//alert(sport_type);
        if(typeof selectedTime != 'undefined')
        {
            var seldate;
            
            if(new Date(selectedTime).getTime() > new Date(new Date().setMonth(6)).getTime())
            {
            	$rootScope.$broadcast("msg" ,{'type':'error','title':'Warning','msg':'Invalid slot selection, Booking Available upto 6 months!!'});
            	return;
            }
            else if(new Date(selectedTime).getTime() <new Date().getTime())
            {
            	$rootScope.$broadcast("msg" ,{'type':'error','title':'Warning','msg':'Invalid slot selection, Past dated booking not available!!'});
            	return;
            }
            else if($scope.months_available[new Date(selectedTime).getMonth()+1] == "0" || $scope.week_days_available[new Date(selectedTime).getDay()+1] == "0")
            {
            	$rootScope.$broadcast("msg" ,{'type':'error','title':'Warning','msg':'Not available!!'});
            	return;
            }
            
            if(sport_type != "1" && !$scope.current_selection_date)
            {
                $scope.show_calendar = false;
                seldate =  selectedTime.toISOString().split("T")[0];
                $scope.sbooked_slots = [];
                AuthService.booking_detail(seldate, seldate, $scope.active_id).then(function(res){
                    if(res['data'].length)
                    {
                        angular.forEach(res['data'][0].booked_time, function(a,b){
                            $scope.sbooked_slots.push(parseInt(a)-6);
                        });
                    }
                    $scope.current_selection_date = seldate;
                    $scope.show_calendar = true;
                });
                return;
            }
            else if(sport_type != "1")
            {
                seldate =  selectedTime;
                $scope.current_selection_date = false;
            }
            else
             {   
             	seldate =  selectedTime.toISOString().split("T")[0];
             	if($scope.bbooked_ddetails.indexOf(new Date(seldate).getTime()))
             	{
             		$rootScope.$broadcast("msg" ,{'type':'error','title':'Warning','msg':'Already booked!!'});
            		return;
             	}
             }

            var indd = $scope.booked_slots.indexOf(seldate);
            if(indd == -1)
            {
                $scope.booked_slots.push(seldate);
                if(sport_type != "1")
                    $scope.show_stet = true;
                $scope.store_time_data[seldate] = angular.copy($scope.sselected_slots);
                $scope.sselected_slots = [];
            }    
            else
                $scope.booked_slots.splice(indd, 1);
        }

        $scope.order_details = [];

        $scope.monthlySource = angular.copy($scope.monthlySourcebk);

        var dataa = $scope.change_start_end2(sport_type, $scope.start_time, $scope.end_time);
        
        $scope.bbooked_ddetails = [];

        angular.forEach($scope.booked_slots, function(v,k){
	    $scope.bbooked_ddetails.push(new Date(v).getTime());
            var sodt1 = new Date(v);
            var sodt = new Date(v);
            var expDate = sodt;
            expDate.setDate(sodt.getDate() + 1);
            $scope.monthlySource.push({
                    title: 'All Day',
                    startTime: sodt1,
                    endTime:  expDate,
                    allDay: true,
                    type: 'selected'
                });

            var mmonth = parseInt($scope.page_data.month) == parseInt(v.split("-")[1]);
            var mindex = $scope.order_details.length ? $scope.order_details[$scope.order_details.length - 1].mindex + 1 : 1;

            if(sport_type != "1")
            {
                var cost = (mmonth ? $scope.page_data.cost : $scope.page_data.next_month_cost);
                var qtyy =  1;//$scope.end_time - $scope.start_time;
                
                angular.forEach($scope.store_time_data[v], function(vv,kk){
                    
                    var dataa = $scope.change_start_end2(sport_type, parseInt(vv)+6, parseInt(vv)+7);
                    
                var data = {sport_type: sport_type, mindex: mindex, sindex: 0, cost: cost,currency:$scope.page_data.currency, name: $scope.page_data.venue_name, venue_id:$scope.page_data.id, facility_id: 0, booking_date:v, booked_time: '', booking_time:dataa.booking_time, booking_start_time: dataa.booking_start_time, booking_end_time: dataa.booking_end_time, quantity: qtyy};
                $scope.order_details.push(data);
                
                angular.forEach($scope.page_data.facilities, function(v1,k1){
                    if(v1.selected)
                    {
                        
                        if(sport_type != "1")
                            var cost = (mmonth ? v1.cost : v1.next_month_cost);
                        else
                            var cost = (mmonth ? v1.cost_per_day : v1.next_month_cost_per_day);
                        var data = {sport_type: sport_type, mindex: mindex, sindex: (k1 + 1), cost: cost, name: v1.facility_name, currency:$scope.page_data.currency,venue_id:$scope.page_data.id, facility_id: v1.id, booking_date:v, booked_time: '', booking_time:dataa.booking_time, booking_start_time: dataa.booking_start_time, booking_end_time: dataa.booking_end_time, quantity: qtyy};
                        $scope.order_details.push(data);
                    }
                });
                
                });
            }    
            else
            {
                var cost = (mmonth ? $scope.page_data.cost_per_day : $scope.page_data.next_month_cost_per_day);
                var qtyy = 1;
                
                    var data = {sport_type: sport_type, mindex: mindex, sindex: 0, cost: cost,currency:$scope.page_data.currency, name: $scope.page_data.venue_name, venue_id:$scope.page_data.id, facility_id: 0, booking_date:v, booked_time: '', booking_time:dataa.booking_time, booking_start_time: dataa.booking_start_time, booking_end_time: dataa.booking_end_time, quantity: qtyy};
                $scope.order_details.push(data);
                
                angular.forEach($scope.page_data.facilities, function(v1,k1){
                if(v1.selected)
                {
                    
                    if(sport_type != "1")
                        var cost = (mmonth ? v1.cost : v1.next_month_cost);
                    else
                        var cost = (mmonth ? v1.cost_per_day : v1.next_month_cost_per_day);
                    var data = {sport_type: sport_type, mindex: mindex, sindex: (k1 + 1), cost: cost, name: v1.facility_name, currency:$scope.page_data.currency,venue_id:$scope.page_data.id, facility_id: v1.id, booking_date:v, booked_time: '', booking_time:dataa.booking_time, booking_start_time: dataa.booking_start_time, booking_end_time: dataa.booking_end_time, quantity: qtyy};
                    $scope.order_details.push(data);
                }
            });
            }    

            
            
            

            
        });

        $scope.booked_slot = $scope.booked_slots.length;
        
        $scope.show_calendar = false;
        $timeout(function(){
            $scope.show_calendar = true;
        }, 1000);
    }

    $scope.monthlySourcefn = function()
    {
        var events = [];
        var date = new Date();
        var firstDay = new Date().toISOString().split('T')[0];
        var lastDay = new Date(date.setMonth(6)).toISOString().split('T')[0];
        
        var firstday_loop = new Date(firstDay).getTime();
        var lastday_loop = new Date(lastDay).getTime();
        while(firstday_loop < lastday_loop)
        {
            if($scope.months_available[new Date(firstday_loop).getMonth()+1] == "0")
            {
                var sodt1 = new Date(firstday_loop);
                var sodt = new Date(firstday_loop);
                var expDate = sodt;
                expDate.setDate(sodt.getDate() + 1);
                
                  events.push({
                        title: 'All Day',
                        startTime: sodt1,
                        endTime:  expDate,
                        allDay: true,
                        type: 'blocked'
                    });
            }
            else if($scope.week_days_available[new Date(firstday_loop).getDay()+1] == "0")
            {
                var sodt1 = new Date(firstday_loop);
                var sodt = new Date(firstday_loop);
                var expDate = sodt;
                expDate.setDate(sodt.getDate() + 1);
                
                  events.push({
                        title: 'All Day',
                        startTime: sodt1,
                        endTime:  expDate,
                        allDay: true,
                        type: 'blocked'
                    });
            }

            firstday_loop = new Date(firstday_loop).setDate(new Date(firstday_loop).getDate() + 1);
        }
        
        AuthService.booking_detail(firstDay, lastDay, $scope.active_id).then(function(res){
         //console.log("df sdf sd"); console.log(res.data.blocked_dates);
          angular.forEach(res.data.blocked_dates, function(v,k){
            var sodt1 = new Date(v);
            var sodt = new Date(v);
            var expDate = sodt;
            expDate.setDate(sodt.getDate() + 1);
            
              events.push({
                    title: 'All Day',
                    startTime: sodt1,
                    endTime:  expDate,
                    allDay: true,
                    type: 'blocked'
                });
             
          });

          angular.forEach(res.data, function(v,k){
                if(k != 'blocked_dates')
                {
                    var sodt1 = new Date(v.booking_date);
                    var sodt = new Date(v.booking_date);
                    var expDate = sodt;
                    expDate.setDate(sodt.getDate() + 1);
                    
                      events.push({
                            title: 'All Day',
                            startTime: sodt1,
                            endTime:  expDate,
                            allDay: true,
                            type: 'booked'
                        });
                }
          });

        });  

        $scope.monthlySourcebk = events;    
        return events;
    };

    
    
    $scope.booked_slot = 0;
    $scope.booked_slots = [];
    $scope.order_details = [];

    $scope.reset_blocked_slots = function(){
        $scope.booked_slot = 0;
        $scope.booked_slots = [];
        $scope.order_details = [];
    $scope.current_selection_date = false;
        $scope.sselected_slots = [];
        
        angular.forEach($scope.week_booking, function(v,k){
            angular.forEach(v, function(v1,k1){
                if(v1 == 2)
                {
                    $scope.week_booking[k][k1] = 0;
                }
            });
        });
    };

    $scope.booked_slot_divide_bydateandtime = function(sp){


        //if(sp == 1)
        //{
            $scope.onTimeSelected();
            return;
        //}

        $scope.booked_slots = [];
        $scope.order_details = [];
        var pdslot;
        angular.forEach($scope.week_booking, function(v,k){
            pdslot = {booked_date:k, booked_time:[]};
            angular.forEach(v, function(v1,k1){
                if(v1 == 2)
                {
                    pdslot['booked_time'].push(k1);
                }
                else if(pdslot['booked_time'].length)
                {
                    $scope.booked_slots.push(pdslot);
                    pdslot = {booked_date:k, booked_time:[]};
                }
            });

            if(pdslot['booked_time'].length)
                $scope.booked_slots.push(pdslot);
        });


        angular.forEach($scope.booked_slots, function(v,k){
            var mmonth = parseInt($scope.page_data.month) == parseInt(v.booked_date.split("-")[1]);
            var dataa = $scope.change_start_end(v.booked_time);
            var mindex = $scope.order_details.length ? $scope.order_details[$scope.order_details.length - 1].mindex + 1 : 1;
            
            var sport_type = $scope.booking_sport;

            if(sport_type != "1")
                var cost = (mmonth ? $scope.page_data.cost : $scope.page_data.next_month_cost);
            else
                var cost = (mmonth ? $scope.page_data.cost_per_day : $scope.page_data.next_month_cost_per_day);

            var data = {sport_type: sport_type, mindex: mindex, sindex: 0, cost: cost,currency:$scope.page_data.currency, name: $scope.page_data.venue_name, venue_id:$scope.page_data.id, facility_id: 0, booking_date:v.booked_date, booked_time: v.booked_time, booking_time:dataa.booking_time, booking_start_time: dataa.booking_start_time, booking_end_time: dataa.booking_end_time, quantity: v.booked_time.length};
            $scope.order_details.push(data);
            angular.forEach($scope.page_data.facilities, function(v1,k1){
                if(v1.selected)
                {
                    
                    if(sport_type != "1")
                        var cost = (mmonth ? v1.cost : v1.next_month_cost);
                    else
                        var cost = (mmonth ? v1.cost_per_day : v1.next_month_cost_per_day);
                    var data = {sport_type: sport_type, mindex: mindex, sindex: (k1 + 1), cost: cost, name: v1.facility_name, currency:$scope.page_data.currency,venue_id:$scope.page_data.id, facility_id: v1.id, booking_date:v.booked_date, booked_time: v.booked_time, booking_time:dataa.booking_time, booking_start_time: dataa.booking_start_time, booking_end_time: dataa.booking_end_time, quantity: v.booked_time.length};
                    $scope.order_details.push(data);
                }
            });
        });
    };

    $scope.book_slot = function(dt, hr)
    {   
        console.log(dt);
        if($scope.week_booking[dt][hr] == 1 && $scope.week_booking[dt][hr] == 3)
            return; 
        if($scope.week_booking[dt][hr] == 2)
        {
            $scope.week_booking[dt][hr] = 0;
            $scope.booked_slot--;
        }
        else if($scope.week_booking[dt][hr] == 0)
        {
            $scope.week_booking[dt][hr] = 2;
            $scope.booked_slot++;
        }

        $scope.booked_slot_divide_bydateandtime();

    };

    $scope.add_to_cart = function(){
        angular.forEach($scope.order_details, function(v,k){
            Session.cart_details.push(v);
        });
        localStorage.setItem('cart_details', JSON.stringify(Session.cart_details));
        $scope.order_details = [];
        $state.go("order");
    };

    $scope.change_start_end = function(booked_time){
        var arr = {booking_start_time:"", booking_end_time:"", booking_time:""};
        
        if(booked_time[0] != 'booked_status')
        {
            arr['booking_start_time'] = booked_time[0];
            arr['booking_end_time'] = booked_time[booked_time.length - 1];

            arr['booking_end_time'] = (parseInt(arr['booking_end_time'])+1) + ":00:00";

            arr['booking_time'] = parseInt(arr['booking_start_time']) > 12 ? (parseInt(arr['booking_start_time']) - 12) + "PM" : parseInt(arr['booking_start_time'])+(parseInt(arr['booking_start_time']) == 12 ? "PM" : "AM");
            arr['booking_time'] += " - ";
            arr['booking_time'] += parseInt(arr['booking_end_time']) > 12 ? (parseInt(arr['booking_end_time']) - 12) + "PM" : parseInt(arr['booking_end_time'])+(parseInt(arr['booking_end_time']) == 12 ? "PM" : "AM");
        }
        
        return arr;
    };

    $scope.change_start_end2 = function(sp, st, et){
        
        if(sp == "1")
        {
            var arr = {booking_start_time: "", booking_end_time: "", booking_time:""};
            return arr;
        }

        var arr = {booking_start_time: st + ":00:00", booking_end_time: et + ":00:00", booking_time:""};
        
        arr['booking_time'] = parseInt(arr['booking_start_time']) > 12 ? (parseInt(arr['booking_start_time']) - 12) + "PM" : parseInt(arr['booking_start_time'])+(parseInt(arr['booking_start_time']) == 12 ? "PM" : "AM");
        arr['booking_time'] += " - ";
        arr['booking_time'] += parseInt(arr['booking_end_time']) > 12 ? (parseInt(arr['booking_end_time']) - 12) + "PM" : parseInt(arr['booking_end_time'])+(parseInt(arr['booking_end_time']) == 12 ? "PM" : "AM");
        
        return arr;
    };

    $scope.total_amount = function(){
        var amt = 0;

        angular.forEach($scope.order_details, function(v,k){
            if($scope.exratecurrency == 'INR')
            amt +=  v.quantity * parseFloat($rootScope.exrate[v.currency]['INR']) * parseFloat(v.cost);
            else if($scope.exratecurrency == 'EUR')
            amt +=  v.quantity * parseFloat($rootScope.exrate[v.currency]['EUR']) * parseFloat(v.cost);
            else if($scope.exratecurrency == 'GBP')
            amt +=  v.quantity * parseFloat($rootScope.exrate[v.currency]['GBP']) * parseFloat(v.cost);
            else if($scope.exratecurrency == 'USD')
            amt +=  v.quantity * parseFloat($rootScope.exrate[v.currency]['USD']) * parseFloat(v.cost);
            else
            amt +=  v.quantity * parseFloat(v.cost);
        });
        return amt.toFixed(2);
    };
    
    $scope.total_amount_wit_tax = function(){
    var ttlamy =  $scope.total_amount(); 
    var taxamt = (10*ttlamy)/100; 
    var res = parseFloat(ttlamy) + parseFloat(taxamt); 
    return res.toFixed(2);
    }
    
    $scope.total_amount_inr = function(){
        var amt = 0;
        angular.forEach($scope.order_details, function(v,k){
           amt +=  v.quantity * parseFloat($rootScope.exrate[v.currency]['INR']) * parseFloat(v.cost);
           /* if($scope.exratecurrency == 'INR')
            amt +=  v.quantity * parseFloat($rootScope.exrate[v.currency]['USD']) * parseFloat(v.cost);
            else if($scope.exratecurrency == 'EUR')
            amt +=  v.quantity * parseFloat($rootScope.exrate[v.currency]['EUR']) * parseFloat(v.cost);
            else if($scope.exratecurrency == 'GBP')
            amt +=  v.quantity * parseFloat($rootScope.exrate[v.currency]['GBP']) * parseFloat(v.cost);
            else if($scope.exratecurrency == 'USD')
            amt +=  v.quantity * parseFloat($rootScope.exrate[v.currency]['USD']) * parseFloat(v.cost);
            else
            amt +=  v.quantity * parseFloat(v.cost);*/

        });
        
        var ttlamy =  amt.toFixed(2);
    var taxamt = (10*ttlamy)/100; 
    var res = parseFloat(ttlamy) + parseFloat(taxamt); 
        return res.toFixed(2);
    };

    $scope.active_state = $state.current.name;
    $scope.active_id = $state.params.venue_id === undefined ? -1 : $state.params.venue_id;
    
    $rootScope.$on('$stateChangeStart', 
    function(event, toState, toParams, fromState, fromParams){ 
        $scope.active_state = toState.name;
        $scope.active_id = toParams.venue_id;
        $scope.actions();
    });
    
    $scope.validDate = function(eventdate){
        console.log(eventdate);
    var res = eventdate.split("-"); 
    var myDate = new Date(res[0], res[1]-1, res[2]);
    var today = new Date();
    var wday = myDate.getDay();
    var mnth = myDate.getMonth();
    
    if (myDate > today) 
    { 
        if($scope.months_available[mnth+1] == 0 && $scope.months_available.length > 0){ return true; }
        if($scope.week_days_available[wday+1] == 1 || $scope.week_days_available.length == 0) { return false; }else{ return true; }  
    
    } else {  return true; }
         
    };


    $scope.otp = '';

    $scope.send_otp = function(){
    
        var ctr_code = Session.user.mobile_country_code.split('');
    if(ctr_code[0] == '+' || ctr_code[0] == '0'){ ctr_code.splice(0,1);}
    var ctrycode = ctr_code.join('');
        
        AuthService.send_otp({"countryCode": ctrycode, "mobileNumber": Session.user.mobile}).then(function(res){
            $scope.$$childTail.otp = res.otp;
            $scope.otp = res.otp;
            console.clear();
        });
    };

    $scope.verify_mobile = function(uotp){
        
        if(uotp == $scope.otp)
        {
            AuthService.verfied_user(Session.user.id).then(function(){
                Session.user.status = '1';
                Session.create(Session.user, true);
                $location.path('/myprofile');
            });
        }
        else
            $scope.$$childTail.msg = "Invalid otp";
    };

    $scope.verify_otp = function(uotp) {
         if(uotp == $scope.otp)
        {
            $scope.otp = '';
            $rootScope.verified_user_id = $rootScope.verify_user;
            $rootScope.user_details = {id: $rootScope.verify_user};

        }
        else
            $rootScope.$broadcast("msg" ,{'type':'error','title':'Error','msg':'Invalid otp'});
    };

  }).directive('compareTo',function() {
    return {
        require: "ngModel",
        scope: {
            otherModelValue: "=compareTo"
        },
        link: function(scope, element, attributes, ngModel) {
             
            ngModel.$validators.compareTo = function(modelValue) {
                return modelValue == scope.otherModelValue;
            };
 
            scope.$watch("otherModelValue", function() {
                ngModel.$validate();
            });
        }
    };
}).directive('ngEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if(event.which === 13) {
                scope.$apply(function (){
                    scope.$eval(attrs.ngEnter);
                });
 
                event.preventDefault();
            }
        });
    };
});