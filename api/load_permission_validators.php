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

// Load all permission validator functions
$permitReflector = new ReflectionClass('PermissionValidators');
$methods = $permitReflector->getMethods();

// Next, get parameter list for each function
$functionsWithParams = array();

foreach ($methods as $method) {
    $function_name = $method->getName();
    // Map the function argument names to their values.  We end up with a dictionary of argument_name => argument_value
    $commentBlock = parseCommentBlock($method->getDocComment());
    if (!$description = $commentBlock['description'])
        $description = "No description available.";
    if (!$parameters = $commentBlock['parameters'])
        $parameters = array();       
    $methodObj = array("description" => $description, "parameters" => array());
    foreach ($method->getParameters() as $param){
        if (isset($parameters[$param->name]))
            $methodObj['parameters'][$param->name] = $parameters[$param->name];
        else
            $methodObj['parameters'][$param->name] = array("type" => "unknown", "description" => "unknown");
    }
    $functionsWithParams[$function_name] = $methodObj;

}

echo json_encode($functionsWithParams);

?>