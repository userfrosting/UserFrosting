<?php
/*

UserFrosting Version: 0.1
By Alex Weissman
Copyright (c) 2014

Based on the UserCake user management system, v2.0.2.
Copyright (c) 2009-2012

UserFrosting, like UserCake, is 100% free and open-source.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){
  // Forward to 404 page
  addAlert("danger", "Whoops, looks like you don't have permission to view that page.");
  header("Location: 404.php");
  exit();
}

setReferralPage($_SERVER['PHP_SELF']);

//Forward the user to their default page if he/she is already logged in
if(isUserLoggedIn()) {
	addAlert("warning", "You're already logged in!");
    header("Location: account.php");
	exit();
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="css/favicon.ico">

    <title>Welcome to FadedGaming!</title>

    <link rel="icon" type="image/x-icon" href="css/favicon.ico" />
    
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/jumbotron-narrow.css" rel="stylesheet">
	
	<!-- Custom theme for fadedgaming.co -->
	<link href="css/fg.css" rel="stylesheet">
	
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap-switch.min.css" type="text/css" />
	 
    <!-- JavaScript -->
    <script src="js/jquery-1.10.2.min.js"></script>
	<script src="js/bootstrap.js"></script>
	<script src="js/userfrosting.js"></script>
	<script src="js/date.min.js"></script>
    <script src="js/handlebars-v1.2.0.js"></script> 

  </head>

  <body>
    <div class="container">
      <div class="header">
        <ul class="nav nav-pills navbar pull-right">
        </ul>
        <h3 class="text-muted">FadedGaming</h3>
      </div>
      
		<article>
			<div class="news-post" style="padding-bottom:5px;">
				<div class="module">
					<div class="module-headernews"> <a href="users/{PAGE_ROW_AUTHOR}" class="avatar pull-left news-avatar"> <img src="//minotar.net/avatar/{PAGE_ROW_AUTHOR}/40"> </a>
						<div style="line-height: 26px;margin-left: 55px;"> <span class="news-header"> <a href="{PAGE_ROW_URL}">{PAGE_ROW_SHORTTITLE}</a> </span> 
							<!-- IF {PHP.usr.isadmin} --> 
								<a href="#" class="small pull-right">Edit</a> 
							<!-- ENDIF --> 
							<br />
							<u><span class="muted"> Posted by: <a href="users/{PAGE_ROW_AUTHOR}"> {PAGE_ROW_AUTHOR} </a> on {PAGE_ROW_DATE} {PAGE_ROW_TIME} {PAGE_ROW_CATPATH} </span></u>
						</div>
					</div>
					<div class="module-content">
						<div style="margin:7px 0 7px 0;">
							<div style="text-align:left;"> {PAGE_ROW_TEXT_CUT} 
							<a href="{PAGE_ROW_URL}" title="{PHP.L.kick_fullstory}" class="btn btn-success btn-block" >{PHP.L.ReadMore}</a>
						</div>
					</div>
				</div>
			</div>
		</article>	
		<article>
			<div class="news-post" style="padding-bottom:5px;">
				<div class="module">
					<div class="module-headernews"> <a href="users/{PAGE_ROW_AUTHOR}" class="avatar pull-left news-avatar"> <img src="//minotar.net/avatar/{PAGE_ROW_AUTHOR}/40"> </a>
						<div style="line-height: 26px;margin-left: 55px;"> <span class="news-header"> <a href="{PAGE_ROW_URL}">{PAGE_ROW_SHORTTITLE}</a> </span> 
							<!-- IF {PHP.usr.isadmin} --> 
								<a href="#" class="small pull-right">Edit</a> 
							<!-- ENDIF --> 
							<br />
							<u><span class="muted"> Posted by: <a href="users/{PAGE_ROW_AUTHOR}"> {PAGE_ROW_AUTHOR} </a> on {PAGE_ROW_DATE} {PAGE_ROW_TIME} {PAGE_ROW_CATPATH} </span></u>
						</div>
					</div>
					<div class="module-content">
						<div style="margin:7px 0 7px 0;">
							<div style="text-align:left;"> {PAGE_ROW_TEXT_CUT} 
							<a href="{PAGE_ROW_URL}" title="{PHP.L.kick_fullstory}" class="btn btn-success btn-block" >{PHP.L.ReadMore}</a>
						</div>
					</div>
				</div>
			</div>
		</article>	
		<article>
			<div class="news-post" style="padding-bottom:5px;">
				<div class="module">
					<div class="module-headernews"> <a href="users/{PAGE_ROW_AUTHOR}" class="avatar pull-left news-avatar"> <img src="//minotar.net/avatar/{PAGE_ROW_AUTHOR}/40"> </a>
						<div style="line-height: 26px;margin-left: 55px;"> <span class="news-header"> <a href="{PAGE_ROW_URL}">{PAGE_ROW_SHORTTITLE}</a> </span> 
							<!-- IF {PHP.usr.isadmin} --> 
								<a href="#" class="small pull-right">Edit</a> 
							<!-- ENDIF --> 
							<br />
							<u><span class="muted"> Posted by: <a href="users/{PAGE_ROW_AUTHOR}"> {PAGE_ROW_AUTHOR} </a> on {PAGE_ROW_DATE} {PAGE_ROW_TIME} {PAGE_ROW_CATPATH} </span></u>
						</div>
					</div>
					<div class="module-content">
						<div style="margin:7px 0 7px 0;">
							<div style="text-align:left;"> {PAGE_ROW_TEXT_CUT} 
							<a href="{PAGE_ROW_URL}" title="{PHP.L.kick_fullstory}" class="btn btn-success btn-block" >{PHP.L.ReadMore}</a>
						</div>
					</div>
				</div>
			</div>
		</article>	
		<article>
			<div class="news-post" style="padding-bottom:5px;">
				<div class="module">
					<div class="module-headernews"> <a href="users/{PAGE_ROW_AUTHOR}" class="avatar pull-left news-avatar"> <img src="//minotar.net/avatar/{PAGE_ROW_AUTHOR}/40"> </a>
						<div style="line-height: 26px;margin-left: 55px;"> <span class="news-header"> <a href="{PAGE_ROW_URL}">{PAGE_ROW_SHORTTITLE}</a> </span> 
							<!-- IF {PHP.usr.isadmin} --> 
								<a href="#" class="small pull-right">Edit</a> 
							<!-- ENDIF --> 
							<br />
							<u><span class="muted"> Posted by: <a href="users/{PAGE_ROW_AUTHOR}"> {PAGE_ROW_AUTHOR} </a> on {PAGE_ROW_DATE} {PAGE_ROW_TIME} {PAGE_ROW_CATPATH} </span></u>
						</div>
					</div>
					<div class="module-content">
						<div style="margin:7px 0 7px 0;">
							<div style="text-align:left;"> {PAGE_ROW_TEXT_CUT} 
							<a href="{PAGE_ROW_URL}" title="{PHP.L.kick_fullstory}" class="btn btn-success btn-block" >{PHP.L.ReadMore}</a>
						</div>
					</div>
				</div>
			</div>
		</article>	
      	
      <div class="footer">
        <p>&copy; FadedGaming.co, 2014</p>
      </div>

    </div> <!-- /container -->

  </body>
</html>

<script>
	$(document).ready(function() {
		alertWidget('display-alerts');
        // Load navigation bar
        $(".navbar").load("header-loggedout.php", function() {
            $(".navbar .navitem-news").addClass('active');
        });
        // Load jumbotron links
        $(".jumbotron-links").load("jumbotron_links.php");     
	});
</script>
