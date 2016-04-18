<?php if(!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

<p>This is the demo for logging in with the Rememberme Library. <br>
  You are seeing this form because you have no active "Remember me" cookie and no
  credentials stored in the session.
</p>
<p>Please log in with the username and password <em>demo</em></p>

<form method="post" action="index.php">
  <label for="username">User Name:</label> <input type="text" name="username" id="username"> <br>
  <label for="password">Password:</label> <input type="password" name="password" id="password"><br>
  <input type="checkbox" id="rememberme" value="1" name="rememberme"> Remember me  <br>
  <input type="submit" value="Log me in">
</form>