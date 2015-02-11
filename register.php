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
require_once("models/config.php");

setReferralPage(getAbsoluteDocumentPath(__FILE__));

if (!userIdExists('1')){
	addAlert("danger", lang("MASTER_ACCOUNT_NOT_EXISTS"));
	header("Location: install/wizard_root_user.php");
	exit();
}

// If registration is disabled, send them back to the home page with an error message
if (!$can_register){
	addAlert("danger", lang("ACCOUNT_REGISTRATION_DISABLED"));
	header("Location: login.php");
	exit();
}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) {
	addAlert("danger", "I'm sorry, you cannot register for an account while logged in.  Please log out first.");
	apiReturnError(false, SITE_ROOT);
}

?>

<!DOCTYPE html>
<html lang="en">
  <?php
	echo renderTemplate("head.html", array("#SITE_ROOT#" => SITE_ROOT, "#SITE_TITLE#" => SITE_TITLE, "#PAGE_TITLE#" => "Register"));
    
    $fields = [
        'user_name' => [
            'type' => 'text',
            'label' => 'Username',
            'icon' => 'fa fa-fw fa-edit',
            'validator' => [
                'minLength' => 1,
                'maxLength' => 25,
                'label' => 'Username'
            ],
            'placeholder' => 'User name'
        ],
        'display_name' => [
            'type' => 'text',
            'label' => 'Display Name',
            'icon' => 'fa fa-fw fa-edit',
            'validator' => [
                'minLength' => 1,
                'maxLength' => 50,
                'label' => 'Display name'
            ],
            'placeholder' => 'Display name'
        ],          
        'email' => [
            'type' => 'text',
            'label' => 'Email',
            'icon' => 'fa fa-fw fa-envelope',
            'validator' => [
                'minLength' => 1,
                'maxLength' => 150,
                'email' => true,
                'label' => 'Email'
            ],
            'placeholder' => 'Email address'
        ],
        'password' => [
            'type' => 'password',
            'label' => 'Password',
            'icon' => 'fa fa-fw fa-key',
            'validator' => [
                'minLength' => 8,
                'maxLength' => 50,
                'label' => 'Password',
                'passwordMatch' => 'passwordc'
            ],
            'placeholder' => '8-50 characters'
        ],
        'passwordc' => [
            'type' => 'password',
            'label' => 'Confirm password',
            'icon' => 'fa fa-fw fa-key',
            'validator' => [
                'minLength' => 8,
                'maxLength' => 50,
                'label' => 'Password'
            ],
            'placeholder' => 'Re-enter your password'
            
        ],
        'captcha' => [
            'type' => 'text',
            'label' => 'Confirm Security Code',
            'icon' => 'fa fa-fw fa-eye',
            'validator' => [
                'minLength' => 1,
                'maxLength' => 50,
                'label' => 'Security code'
            ],
            'placeholder' => "Enter the code below, human!"            
        ]
    ];
    
    $captcha = generateCaptcha();
    
    $template = "
        <form name='newUser' class='form-horizontal' id='newUser' role='form' action='api/create_user.php' method='post'>
		  <div class='row'>
			<div id='display-alerts' class='col-lg-12'>
		  
			</div>
		  </div>		
		  <div class='row'>
			<div class='col-sm-12'>
                {{user_name}}
            </div>
		  </div>
		  <div class='row'>
            <div class='col-sm-12'>
                {{display_name}}
            </div>
		  </div>
		  <div class='row'>
			<div class='col-sm-12'>
                {{email}}
            </div>
		  </div>		  
		  <div class='row'>
            <div class='col-sm-12'>
                {{password}}
            </div>
		  </div>
		  <div class='row'>
            <div class='col-sm-12'>
                {{passwordc}}
            </div>
		  </div>
		  <div class='row'>
            <div class='col-sm-12'>
                {{captcha}}
            </div>
          </div>
          <div class='form-group'>
            <div class='col-sm-12'>
                <img src='$captcha' id='captcha'>
            </div>
		  </div>
		  <br>
		  <div class='form-group'>
			<div class='col-sm-12'>
			  <button type='submit' class='btn btn-success submit' value='Register'>Register</button>
			</div>
		  </div>
          <div class='collapse'>
            <label>Spiderbro: Don't change me bro, I'm tryin'a catch some flies!</label>
            <input name='spiderbro' id='spiderbro' value='http://'/>
          </div>          
		</form>";
    
    $fb = new FormBuilder($template, $fields, [], [], true);
    
  ?>

  <body>
    <div class="container">
      <div class="header">
        <ul class="nav nav-pills navbar pull-right">
        </ul>
        <h3 class="text-muted">UserFrosting</h3>
      </div>
      <div class="jumbotron">
        <h1>Let's get started!</h1>
        <p class="lead">Registration is fast and simple.</p>
        <?php echo $fb->render(); ?>
        
	  </div>	
      <?php echo renderTemplate("footer.html"); ?>

    </div> <!-- /container -->

<script>
	$(document).ready(function() {
		// Load navigation bar
		$(".navbar").load("header-loggedout.php", function() {
            $(".navbar .navitem-register").addClass('active');
        });
		
		// Process submission
        $("form[name='newUser']").submit(function(e){
			e.preventDefault();
            var form = $(this);
            var errorMessages = validateFormFields('newUser');
			if (errorMessages.length > 0) {
				$('#display-alerts').html("");
				$.each(errorMessages, function (idx, msg) {
					$('#display-alerts').append("<div class='alert alert-danger'>" + msg + "</div>");
				});	
			} else {
                // Process form                    
                // Serialize and post to the backend script in ajax mode
                var serializedData = form.serialize();
                serializedData += '&ajaxMode=true';     
                //console.log(serializedData);
            
                var url = APIPATH + "create_user.php";
                $.ajax({  
                  type: "POST",  
                  url: url,  
                  data: serializedData
                }).done(function(result) {
                  var resultJSON = processJSONResult(result);
                  if (resultJSON['errors'] && resultJSON['errors'] > 0){
                        console.log("error");
						// Reload captcha
						var img_src = APIPATH + 'generate_captcha.php?' + new Date().getTime();
                        $.ajax({  
                          type: "GET",  
                          url: img_src,  
                          dataType: "text"
                        }).done(function(result) { 
                            $('#captcha').attr('src', result);
                            form.find('input[name="captcha"]' ).val("");
                            alertWidget('display-alerts');
                            return;
                        });
                  } else {
                    window.location.replace('login.php');
                  }
                });   
            }
		});
	});
</script>
</body>
</html>
