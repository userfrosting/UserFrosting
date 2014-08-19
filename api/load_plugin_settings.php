<?php
/*

UserFrosting Version: 0.2.0
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

set_error_handler('logAllErrors');

// User must be logged in
if (!isUserLoggedIn()){
    addAlert("danger", "You must be logged in to access this resource.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}
$form = "";
//Retrieve settings
if ($result = fetchConfigParametersPlugins()){
    /*
    $form ="<div class='panel panel-primary'>
            <div class='panel-heading'>
            <h3 class='panel-title'>Plugin Configurations</h3>
            </div>
            <div class='panel-body'>
            <form class='form-horizontal' role='form' name='pluginConfiguration' id='pluginConfiguration' action='../api/update_plugin_settings.php' method='post'>";

    foreach($result as $r => $v){
                //ChromePhp::log($v);
                if ($v['binary'] >= 1) {
                    // Assume this should be a bootstrap switch

                    $form .= "<div class='form-group'><label for='".$v['name']."' class='col-sm-4 control-label'>".$v['name']."</label>
                    <div class='col-sm-8'>";
                    //var settingInt = parseInt(setting);

                    if($v['value'] > 0 ){
                        $form .= "<input type='checkbox' id ='".$v['name']."' name='".$v['variable']."' onchange='updateCheckbox(this)' checked />";
                    }else{
                        $form .= "<input type='checkbox' id ='".$v['name']."' name='".$v['variable']."' onchange='updateCheckbox(this)'/>";
                    }
                        $form .= "<br><small>". $v['variable'] ."</small>
                    </div>
                    </div>";
                    //console.log("binary value " + setting['name'] + " ~ " + setting['value']);
                }else{
                    // Assume this should be a text box
                    $form .= "<div class='form-group'><label for='".$v['name']."' class='col-sm-4 control-label'>".$v['name']."</label>
                    <div class='col-sm-8'>
                    <input type='text' id='".$v['name']."' class='form-control' name='".$v['name']."' value='".$v['value']."' onChange='updateTextbox(this)'/>
                    </div>
                    </div>";
                    //console.log("non binary value " + setting['name'] + " ~ " + setting['value']);
                }
    }

    $form .="<script>
    function updateCheckbox(cb){
        var name = cb.name;
        var value = cb.value;

        console.log(name + ' - ' + value);
    }
    function updateTextbox(tb){
        var name = tb.name;
        var value = tb.value;

        console.log(tb.name + ' - ' + tb.value);
    }
    </script>";

    $form .="<div class='form-group'>
            <div class='col-sm-offset-4 col-sm-8'>
            <!--<input class='btn btn-success submit' type='button' onclick='updatePlugin()' value='Update'>-->
            <button type='submit' class='btn btn-success submit' value='Update'>Update</button>
            </div>
            </div>
            </form>
            </div>
            </div>";*/
    //echo json_encode(array("errors" => 1, "successes" => 0));
    //exit();
}

restore_error_handler();

echo json_encode($result, JSON_FORCE_OBJECT);