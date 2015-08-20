angular.module('filters',[]).filter('extractSender',
    function () {
        return function (input) {
            return input.split("<")[1].split(">")[0];
        };
    }
);