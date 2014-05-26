<?php

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