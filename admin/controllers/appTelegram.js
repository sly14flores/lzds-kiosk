var app = angular.module('appTelegram',['account-module']);

app.controller('appTelegramCtrl', function($scope, $http) {

  $scope.activeTemplate = "views/appTelegrams.html";

  $scope.chats = [];

  const url = 'https://api.telegram.org/bot5910632478:AAFvQtx_zMPYzhJmOXKuQWlD6GLuHhOC_Tk/getUpdates';

  $http.get(url).then(function mySucces(response) {
    $scope.chats = response?.data?.result;
    console.log(response?.data?.result);
  }, function myError(response) {
    $scope.chats = [];
  });

});