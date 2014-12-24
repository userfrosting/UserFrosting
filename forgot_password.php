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

$validate = new Validator();
$token = $validate->optionalGetVar('confirm');
$confirmAjax = 0;

if(!empty($token)){$confirmAjax = 1;}else{$confirmAjax = 0;}

global $token_timeout;
?>

<!DOCTYPE html>
<html lang="en">
  <?php
	echo renderTemplate("head.html", array("#SITE_ROOT#" => SITE_ROOT, "#SITE_TITLE#" => SITE_TITLE, "#PAGE_TITLE#" => "Reset Password"));
  ?>

<body>
<div class="container">
    <div class="header">
        <ul class="nav nav-pills navbar pull-right">
        </ul>
        <h3 class="text-muted">UserFrosting</h3>
    </div>
    <div class="jumbotron">
        <h1>Reset Password</h1>
        <?php
        if(!$token){
            echo'
          <p class="lead">
            Please enter your username and the email address you used to sign up.
            A link with instructions to reset your password will be emailed to you.
        </p>';
        }else{
            echo'
          <p class="lead">
            Please enter your username and your new password to continue.
        </p>';
        }?>

        <form class='form-horizontal' role='form' name='reset_password' action='api/user_reset_password.php' method='post'>
            <div class="row">
                <div id='display-alerts' class="col-lg-12">

                </div>
            </div>
            <div class="form-group">
                <div class="col-md-offset-3 col-md-6">
                    <input type="text" class="form-control" placeholder="Username" name = 'username' value=''>
                </div>
            </div>
            <?php
            if(!$token){

                echo '<div class="form-group">
                  <div class="col-md-offset-3 col-md-6">
                      <input type="email" class="form-control" placeholder="Email" name=\'email\' value=\'\'>
                  </div>
              </div>';
            }else{
                echo '<div class="form-group">
                  <div class="col-md-offset-3 col-md-6">
                      <input type="password" class="form-control" placeholder="New Password" name = \'password\' value=\'\'>
                  </div>
              </div>
              <div class="form-group">
                  <div class="col-md-offset-3 col-md-6">
                      <input type="password" class="form-control" placeholder="Password Confirm" name = \'passwordc\' value=\'\'>
                  </div>
              </div>
              <div class="form-group">
                  <div class="col-md-offset-3 col-md-6">
                      <input type="hidden" class="form-control" placeholder="Token" name = \'token\' value=\''.$token.'\'>
                  </div>
              </div>';
            } ?>

            <div class="form-group">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-success submit" value='Reset'>Reset Password</button>
                </div>
            </div>
        </form>
    </div>
      <?php echo renderTemplate("footer.html"); ?>

</div> <!-- /container -->
<script>
    $(document).ready(function() {
        // Load the header
        $(".navbar").load("header-loggedout.php", function() {
            $(".navbar .navitem-login").addClass('active');
        });

        alertWidget('display-alerts');

        $("form[name='reset_password']").submit(function(e){
            var form = $(this);
            var url = APIPATH + 'user_reset_password.php';
            var confirm = <?php echo $confirmAjax; ?>;

            if(confirm==0)
            {
                var formdata = {
                    username:	form.find('input[name="username"]').val(),
                    email:		form.find('input[name="email"]').val(),
                    initial:    "1",
                    ajaxMode:	"true"
                }
            }
            else
            {
                var formdata = {
                    username:	form.find('input[name="username"]').val(),
                    password:	form.find('input[name="password"]').val(),
                    passwordc:  form.find('input[name="passwordc"]').val(),
                    token:      form.find('input[name="token"]').val(),
                    ajaxMode:	"true"
                }
            }
            $.ajax({
                type: "POST",
                url: url,
                data: formdata
            }).done(function(result) {
                resultJSON = processJSONResult(result);
                alertWidget('display-alerts');
            });
            // Prevent form from submitting twice
            e.preventDefault();
        });
    });
</script>
</body>
</html>
