var controllers = angular.module('controllers',[]);

controllers.controller('templateCtrl',['$scope','$http', function ($scope,$http) {
    $scope.mailboxes = [];
    $scope.getMailboxList = function () {
        $http.get("/api/MailboxList").then(function (response) {
            $scope.mailboxes = response.data;
        });
    };

    $scope.getMailboxList();

    $scope.emails = [];
    $http.get("/api").then(function (response) {
        $scope.emails = response.data;
        $scope.emailCount = Object.keys($scope.emails).length;
    });
}]);

controllers.controller('emailsCtrl',['$routeParams','$scope','$http', function ($routeParams,$scope,$http) {




   // $scope.emails = Api.queryMails();

    $scope.sentEmails = [];

    $scope.composeEmail = {};

    $scope.isEmailPopupVisible = false;
    $scope.isSendPopupVisible = false;

    $scope.activeTab = "inbox";

    $scope.viewEmail = function () {
        $http.get("/api/emaillist/"+$routeParams.id).then(function (response) {
            $scope.emails = response.data;
            $scope.emailCount = $scope.emails.length;
        });
    };
    $scope.viewEmail();

    $scope.go = function (email) {

    };

    $scope.showSendPopup = function () {

        $scope.isSendPopupVisible = true;
    };
    $scope.closeSendPopup = function () {
        $scope.composeEmail = {};
        $scope.isSendPopupVisible = false;
    };

    $scope.showPopup = function (email) {
        $scope.isEmailPopupVisible = true;
        $scope.selectedEmail = email;
    };
    $scope.hidePopup = function () {
        $scope.isEmailPopupVisible = false;
    };

    $scope.sendEmail = function () {
        $scope.composeEmail.date = new Date();
        $scope.composeEmail.from = 'me';
        $scope.sentEmails.push($scope.composeEmail);
        $scope.closeSendPopup();
    };
    
    $scope.forward = function () {
        $scope.composeEmail.subject = $scope.selectedEmail.subject;
        $scope.composeEmail.body =  $scope.selectedEmail.body;
        $scope.hidePopup();
        $scope.showSendPopup();
    };
    
    $scope.reply = function () {
        $scope.hidePopup();
        $scope.composeEmail.subject = $scope.selectedEmail.subject;
        $scope.composeEmail.to =  $scope.selectedEmail.from;
        $scope.composeEmail.body =  $scope.selectedEmail.body;
        $scope.showSendPopup();

    }
}]);

controllers.controller('emailDetailsCtrl',['$routeParams','$scope','$http','$sce', function ($routeParams,$scope,$http,$sce) {
   // $scope.mail = [];
    $scope.mailhtml = "";
    $scope.mailUid = $routeParams.mailUid;
    $http.get("/api/maildetails/"+$routeParams.mailUid).then(function (response) {
      /*  $scope.mail = response.data;
        $scope.mailhtml = $sce.trustAsHtml($scope.mail.html);*/
        $scope.mailhtml = $sce.trustAsHtml(response.data);
    });
}]);

controllers.controller('addMailboxCtrl', ['$scope', '$http', function ($scope, $http) {
    $scope.defaults = [
        {name:'Other', email:'', password:'', server:'', port:993, ssl:false},
        {name:'Gmail', email:'@gmail.com', password:'', server:'imap.gmail.com', port:993, ssl:true},
        {name:'wp.pl', email:'@wp.pl', password:'', server:'imap.gmail.com', port:993, ssl:true}
    ];


    $scope.mailbox = $scope.defaults[0];
    $scope.createMailboxRequest = function () {
        $scope.model = {
            email: $scope.mailbox.email, password: $scope.mailbox.password, server:$scope.mailbox.server, port:$scope.mailbox.port, ssl:$scope.mailbox.ssl
        };
        $http.post('/api/addmailbox',$scope.model)
            .success(function (data) {
                alert(data);
            })
            .error(function (data) {
                alert("Error:"+data);
            });
    };
}]);

