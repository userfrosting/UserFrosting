<?php
/*

UserFrosting Version: 0.2.2
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

require_once("../models/config.php");

// Request method: GET
$ajax = checkRequestMode("get");

if (!securePage(__FILE__)){
    apiReturnError($ajax);
}

setReferralPage(getAbsoluteDocumentPath(__FILE__));
?>

<!DOCTYPE html>
<html lang="en">
  <?php
  	echo renderAccountPageHeader(array("#SITE_ROOT#" => SITE_ROOT, "#SITE_TITLE#" => SITE_TITLE, "#PAGE_TITLE#" => "Authorization Management"));
  ?>
  <body>

    <div id="wrapper">

      <!-- Sidebar -->
        <?php
          echo renderMenu("site-pages");
        ?>
        
      <div id="page-wrapper">
	  	<div class="row">
		  <div id='display-alerts' class="col-lg-12">
  
		  </div>
		</div>
		<div class="row">
		  <div id='display-alerts-instant' class="col-lg-12">
  
		  </div>
		</div>
		<div class="row">
		  <div class="col-lg-12">
			<h2>Group-level action authorization</h2>
			<div class='alert alert-info'>This feature allows you to specify group-level permissions for specific types of actions (e.g. create users, delete users, create groups, etc).  You can also specify certain contexts for these permissions through the use of permits.  For example, you could specify a permit that only allows deleting users in certain groups.</div>
			<div id="widget-group-access">
			</div>
		  </div>
		</div> 
		<div class="row">
		  <div class="col-lg-12">
			<h2>User-level action authorization</h2>
			<div class='alert alert-info'>This feature allows you to specify user-level permissions for specific types of actions.  User-level permissions are applied in parallel with group permissions, i.e. a user will be granted access if they have been given permission at the user level, OR if they belong to a group that has been granted permission.</div>
			<div id="widget-user-access">
			</div>
		  </div>
		</div>
		<div class="row">
		  <div class="col-lg-12">
			<h2>Page-level authorization</h2>
			<div id="widget-site-pages">
			
			</div>
		  </div>
		</div>  
	  </div>
	</div>
	
	</div>
	</div>
	<script src="../js/widget-pages.js"></script>
	<script src="../js/widget-permits.js"></script>	    
	<script>
        $(document).ready(function() {          

		  alertWidget('display-alerts');
		  
		  actionPermitsWidget('widget-group-access', {type: 'group'});
		  actionPermitsWidget('widget-user-access', {type: 'user'});
		  sitePagesWidget('widget-site-pages', { display_errors_id: 'display-alerts-instant'});
		});
	</script>
  </body>
</html>