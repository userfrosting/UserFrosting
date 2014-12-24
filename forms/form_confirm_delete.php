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

$validator = new Validator();

// Request method: GET box_id, title, message, confirm
$box_id = $validator->requiredGetVar('box_id');
$title = $validator->requiredGetVar('title');
$message = $validator->requiredGetVar('message');
$confirm = $validator->requiredGetVar('confirm');

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

if (count($validator->errors) > 0){
  apiReturnError($ajax, getReferralPage()); 
}

$response = "
<div id='$box_id' class='modal fade'>
  <div class='modal-dialog modal-sm'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
        <h4 class='modal-title'>$title</h4>
      </div>
      <div class='modal-body'>
        <div class='dialog-alert'>
        </div>
        <h4>$message<br><small>This action cannot be undone.</small></h4>
        <br>
        <div class='btn-group-action'>
          <button type='button' class='btn btn-danger btn-lg btn-block btn-confirm-delete'>$confirm</button>
          <button type='button' class='btn btn-default btn-lg btn-block' data-dismiss='modal'>Cancel</button>
        </div>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
";

echo json_encode(array("data" => $response), JSON_FORCE_OBJECT);
