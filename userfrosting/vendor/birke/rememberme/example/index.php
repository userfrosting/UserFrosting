<?php
// Include PHP before any content is generated to we can set cookies
// Sets the $content variable with the dynamic page content
include "./action.php";
?>
<!doctype html>

<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Rememberme PHP library test</title>
  <meta name="author" content="Gabriel Birke">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/style.css?v=2">
</head>

<body>

  <div id="container">
    <header>
      <h1>Rememberme PHP library test</h1>
    </header>
    
    <div id="main">
      <?php
      // Output generated content
      echo $content;
      ?>
    </div>
    
    <footer>

    </footer>
  </div> <!-- end of #container -->
  
</body>
</html>