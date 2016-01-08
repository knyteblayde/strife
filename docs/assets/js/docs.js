var myApp = angular.module('app', ['ngRoute', 'ngAnimate'])

    .controller('DocsController', function ($scope, $location) {
        $scope.title = $location.path().replace('/', '');
        $scope.docs = [];

        switch ($scope.title) {
            case "" || "overview":
                $scope.docs[0] = true;
                break;

            case "installation":
                $scope.docs[1] = true;
                break;

            case "configuration":
                $scope.docs[2] = true;
                break;

            case "environment":
                $scope.docs[3] = true;
                break;

            case "routing":
                $scope.docs[4] = true;
                break;

            case "controllers":
                $scope.docs[5] = true;
                break;

            case "views":
                $scope.docs[6] = true;
                break;

            case "database":
                $scope.docs[7] = true;
                break;

            case "models":
                $scope.docs[8] = true;
                break;

            case "querybuilder":
                $scope.docs[9] = true;
                break;

            case "migrations":
                $scope.docs[10] = true;
                break;

            case "formbuilder":
                $scope.docs[11] = true;
                break;

            case "requests":
                $scope.docs[12] = true;
                break;

            case "auth":
                $scope.docs[13] = true;
                break;

            case "hash":
                $scope.docs[14] = true;
                break;

            case "commandline":
                $scope.docs[15] = true;
                break;

            default:
                $scope.docs[0] = true;
                break;
        }

        $scope.changePage = function ($index) {
            $scope.docs = [];
            $scope.docs[$index] = true;
        }
    });