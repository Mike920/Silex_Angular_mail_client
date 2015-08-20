/**
 * Created by Admin on 2015-02-01.
 */
var services = angular.module('services',['ngResource']);

services.factory('Api',['$resource', function ($resource) {
    return $resource('/api',{},{
        queryMails: {method:'GET', isArray:true}
    });
}]);
