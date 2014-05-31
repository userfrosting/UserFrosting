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

# The Regular Expression for Function Declarations
$functionFinder = '/function[\s\n]+(\S+)[\s\n]*\(/';
# Init an Array to hold the Function Names
$functionArray = array();
# Load the Content of the PHP File
$fileContents = file_get_contents( '../models/secure_functions.php' );

# Apply the Regular Expression to the PHP File Contents
preg_match_all( $functionFinder , $fileContents , $functionArray );

# If we have a Result, Tidy It Up
if( count( $functionArray )>1 ){
  # Grab Element 1, as it has the Matches
  $functionArray = $functionArray[1];
}

// Next, get parameter list for each function
$functionsWithParams = array();
foreach ($functionArray as $function) {
    // Map the function argument names to their values.  We end up with a dictionary of argument_name => argument_value
    $method = new ReflectionFunction($function);
    $methodObj = array("name" => $function, "parameters" => array());
    foreach ($method->getParameters() as $param){
        $methodObj['parameters'][] = $param->name;
    }
    $functionsWithParams[] = $methodObj;
    echo json_encode($methodObj);
}

return $functionsWithParams;

?>