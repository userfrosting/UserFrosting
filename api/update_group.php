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

// Update a permission group
// Request method: POST

require_once("../models/config.php");

set_error_handler('logAllErrors');

// Request method: POST
$ajax = checkRequestMode("post");

// User must be logged in
checkLoggedInUser($ajax);

// Update a group, specified by id, with the given group name, is_default setting, and home page id.
// POST: group_id, [group_name, is_default, home_page_id]

$validator = new Validator();
$group_id = $validator->requiredPostVar('group_id');

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

if (count($validator->errors) > 0){
    apiReturnError($ajax, getReferralPage());
}

// Fetch data for this group
$group = fetchGroupDetails($group_id);
if(!$group){
  apiReturnError($ajax, getReferralPage());
}

$group_name = $validator->optionalPostVar('group_name');
if (!$group_name){
  $group_name = $group['name'];
}

$is_default = $validator->optionalPostVar('is_default');
if ($is_default === null){
  $is_default = $group['is_default'];
}

$home_page_id = $validator->optionalPostVar('home_page_id');
if (!$home_page_id){
  $home_page_id = $group['home_page_id'];
}

if (!updateGroup($group_id, $group_name, $is_default, $home_page_id)){
  apiReturnError($ajax, getReferralPage());
}

restore_error_handler();

apiReturnSuccess($ajax, getReferralPage());

?>
