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

// Request method: GET

require_once("models/config.php");

if (!securePage($_SERVER['PHP_SELF'])){
    // Generate AJAX error
    addAlert("danger", "Whoops, looks like you don't have permission to access this component.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

?>

<div class='dialog-alert'>
</div>
<div class='row'>
    <div class='col-md-6'>
    <h5>Username</h5>
    <div class='input-group'>
        <span class='input-group-addon'><i class='fa fa-edit'></i></span>
        <input type='text' class='form-control' name='user_name' data-validate='{"minLength": 1, "maxLength": 25, "label": "Username" }'>
    </div>
    </div>
    <div class='col-md-6'>
    <h5>Display Name</h5>
    <div class='input-group'>
        <span class='input-group-addon'><i class='fa fa-edit'></i></span>
        <input type='text' class='form-control' name='display_name' data-validate='{"minLength": 1, "maxLength": 50, "label": "Display name" }'>
    </div>
    </div>
</div>
<div class='row'>
    <div class='col-md-6'>
    <h5>Email</h5>
    <div class='input-group'>
        <span class='input-group-addon'><a id='email-link' href=''><i class='fa fa-envelope'></i></a></span>
        <input type='text' class='form-control' name='email' data-validate='{"email": true, "label": "Email" }'>
    </div>
    </div>
    <div class='col-md-6'>
    <h5>Title</h5>
    <div class='input-group'>
        <span class='input-group-addon'><i class='fa fa-edit'></i></span>
        <input type='text' class='form-control' name='user_title' data-validate='{"minLength": 1, "maxLength": 100, "label": "Title" }'>
    </div>
    </div>
</div>
<div class='input-group-dates'>


</div>
<div class='row'>
    <div class='col-md-6 input-group-password'>
    </div>
    <div class='col-md-6'>
        <h5>Permission Groups</h5>
        <ul class='list-group permission-summary-rows'>
    
        </ul>
    </div>
</div>
<br>
<div class='row btn-group-action'>
</div>
