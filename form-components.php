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
if (!securePage($_SERVER['PHP_SELF'])){die();}
?>  

<div id='btn-group-create-user' class='col-md-12'>
    <button class='btn btn-lg btn-success btn-create-user'>Create user</button>
    <button class='btn btn-lg btn-link pull-right' data-dismiss='modal'>Cancel</button>
</div>

<div id='btn-group-update-user' class='col-md-12'>
    <button class='btn btn-lg btn-success btn-update-user'>Update user</button>
    <button class='btn btn-lg btn-link pull-right' data-dismiss='modal'>Cancel</button>
</div>

<div id='input-group-create-user-password'>
    <div class='input-group'>
        <h5>Password</h5>
        <div class='input-group'>
            <span class='input-group-addon'><i class='fa fa-lock'></i></span>
            <input type='password' name='password' class='form-control' data-validate='{"minLength": 8, "maxLength": 50, "passwordMatch": "passwordc", "label": "Password"}'>
        </div>
    </div>
    <div class='input-group'>
        <h5>Confirm password</h5>
        <div class='input-group'>
            <span class='input-group-addon'><i class='fa fa-lock'></i></span>
            <input type='password' name='passwordc' class='form-control' data-validate='{"minLength": 8, "maxLength": 50, "label": "Confirm password"}'>
        </div>
    </div>         
</div>

<div id='input-group-display-user-dates'>
<div class='row'>
    <div class='col-md-6'>
    <h5>Last Sign-in</h5>
    <div class='input-group optional'>
        <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
        <input type='text' class='form-control' name='last_sign_in_date'>
    </div>
    </div>
    <div class='col-md-6'>
    <h5>Registered Since</h5>
    <div class='input-group optional'>
        <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
        <input type='text' class='form-control' name='sign_up_date'>
    </div>
    </div>
</div>
</div>
