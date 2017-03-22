'use strict';

/**
 * @ngdoc function
 * @name yapp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of yapp
 */
angular.module('yapp')
  .controller('DashboardCtrl', function($scope, $timeout, $state, AuthService, $location, Session, $rootScope) {
    
        $scope.user = Session.user;
        $scope.meeting = {};
        $scope.vm = {};

        var events = localStorage.getItem('calendar');
        events = events ? JSON.parse(events) : [];

        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listWeek'
            },
            editable: true,
            eventLimit: true,
            navLinks: true,
            selectable: true,
            select: function(start, end, jsEvent, view){
                $("#NewMeetingModal").modal("show");
                $scope.$apply(function(){
                    $scope.meeting.start = start.format();
                    $scope.meeting.end = end.format();
                });
            },
            eventClick: function(calEvent, jsEvent, view){
                $("#MeetingModal").modal("show");
                $scope.$apply(function(){
                    $scope.vm = calEvent;
                });
            },
            events: events
        });

        $scope.add_meeting = function(){
            events.push(angular.copy($scope.meeting));
            $('#calendar').fullCalendar('renderEvents', [angular.copy($scope.meeting)]);
            $scope.meeting = {};
            localStorage.setItem('calendar', JSON.stringify(events));
        };

  });
