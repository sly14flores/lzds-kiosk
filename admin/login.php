<!DOCTYPE html>
<html >
<head>
<meta charset="UTF-8">
<title>Login - Attendance Monitoring System</title>
<link rel="icon" href="../favicon.ico">
<link rel='stylesheet prefetch' href='http://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900|RobotoDraft:400,100,300,500,700,900'>
<link rel="stylesheet prefetch" href="font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/style.css">  
</head>

<body ng-app="appLogin" ng-controller="appLoginCtrl">

<div class="pen-title">
  <h1>Attendance Monitoring System</h1><span>Lord of Zion Divine School</span>
</div>
<!-- Form Module-->
<div class="module form-module">
  <div class="toggle"><i class="fa fa-user"></i></div>
  <div class="form">
    <h2>Login to your account</h2>
    <form ng-submit="login()">
      <input type="text" placeholder="Username" ng-model="account.username" autofocus>
      <input type="password" placeholder="Password" ng-model="account.password">
	  <div class="alert alert-danger" ng-show="views.incorrect">Invalid username or password</div>
      <button type="submit">Login</button>
    </form>
  </div>
  <div class="cta"><a href="javascript:;">ZionTech</a></div>
</div>
<script src="angularjs/angular.min.js"></script>
<script src="controllers/login.js"></script>

</body>
</html>
