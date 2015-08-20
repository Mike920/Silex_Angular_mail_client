var app = angular.module('application',['ngRoute','ngSanitize','controllers','services','filters'])
    .config(function($interpolateProvider){
        $interpolateProvider.startSymbol('{[').endSymbol(']}');
    });

app.config(['$routeProvider',
    function ($routeProvider) {
        $routeProvider.
            when('/mails',{
                templateUrl: 'partials/mail-list.html',
                controller: 'emailsCtrl'
            }).
            when('/inbox/:id',{
                templateUrl: 'partials/mail-list.html',
                controller: 'emailsCtrl'
            }).
            when('/mails/add',{
                templateUrl: 'partials/add-mailbox.html',
                controller: 'addMailboxCtrl'
            }).
            when('/mails/:mailUid',{
                templateUrl: 'partials/mail-details.html',
                controller: 'emailDetailsCtrl'
            }).
            otherwise({
                redirectTo: '/mails'
            });
    }
]);