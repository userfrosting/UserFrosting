<meta charset="utf-8">
<?php

require_once("fortress/config-fortress.php");

/******** Do this in a project-wide config file ********/
// Start the session
session_start();
// Set the message stream
if (!isset($_SESSION['Fortress']['alerts']))
    $_SESSION['Fortress']['alerts'] = new Fortress\MessageStream();
$ms = $_SESSION['Fortress']['alerts'];

// Set the translation path
Fortress\MessageTranslator::setTranslationTable("fortress/locale/en_US.php");

/*******************************************************/

// Test the error stream and reset
echo "<h2>Current message stream</h2>";
echo "<pre>";
print_r($ms->messages());
echo "</pre>";
$ms->resetMessageStream();

// Load the request schema
$requestSchema = new Fortress\RequestSchema("fortress/schema/forms/register.json");

// POST request
$rf = new Fortress\HTTPRequestFortress($ms, $requestSchema, $_GET);
// Remove csrf_token from the request data, if specified
$rf->removeFields(['csrf_token']);

// Sanitize, and print sanitized data for demo purposes
$rf->sanitize(true, "error");

echo "<h2>Sanitized data</h2>";
echo "<pre>";
print_r($rf->data());
echo "</pre>";

// Validate.  Normally we'd want to halt on validation errors.  But for this demo, we will simply print the message stream.
if (!$rf->validate(true)) {
    $ms->addMessageTranslated("danger", "Validation failed for {{placeholder}}", ["placeholder" => "the form"]);
}

// Test client validators
$clientVal = new Fortress\ClientSideValidator("fortress/schema/forms/register.json");
echo "<h2>Client-side validation schema (JSON)</h2>";
echo "<pre>";
print_r($clientVal->formValidationRulesJson());
echo "</pre>";

// Create a new group with the filtered data
$data = $rf->data();

if (!yourFunctionHere($data)){
    exit();
}

// If we've made it this far, success!


?>