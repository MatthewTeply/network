<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

<h1>Welcome</h1>

<form action="users.inc.php" method="POST">
	<input type="text" name="uid" placeholder="Username"><br>
	<input type="text" name="first" placeholder="First Name"><br>
	<input type="text" name="last" placeholder="Last Name"><br>
	<input type="password" name="pwd" placeholder="Password"><br>
	<input type="email" name="em" placeholder="E-mail"><br>
	<button type="submit" name="setUser_subm">Signup</button>
</form>

<form action="users.inc.php" method="POST">
	<input type="text" name="uid"><br>
	<input type="password" name="pwd"><br>
	<button type="submit" name="getUser_subm">Login</button>
</form>

</body>
</html>