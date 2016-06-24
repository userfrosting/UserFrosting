<meta charset="utf-8">
<?php

require_once("../vendor/autoload.php");

\Valitron\Validator::langDir("../vendor/vlucas/valitron/lang");

// Create a message translator
$translator = new UserFrosting\I18n\MessageTranslator();
// Set the translation paths
$translator->setTranslationTable("locale/en_US.php");
$translator->setDefaultTable("locale/en_US.php");

/*******************************************************/

// Load the request schema
$schema = new UserFrosting\Fortress\RequestSchema("schema/forms/register.json");

$schema->addValidator("puppies", "required");

//$schema->setTransformations("puppies", "purge");

$schema->addValidator("minions", "range", [
    "min" => 0,
    "max" => 20,
    "message" => "Not enough minions"
]);

$schema->addValidator("email", "length", [
    "min" => 1,
    "max" => 100,
    "message" => "ACCOUNT_EMAIL_CHAR_LIMIT"
]);

$transformer = new \UserFrosting\Fortress\RequestDataTransformer($schema);

// Transform, and print transformed data for demo purposes
$transformedData = $transformer->transform([
    "puppies" => "<script>I'm definitely really a puppy  </script>0  ",
    "horses" => "seven pretty horses"
], "skip");

echo "<h2>Transformed data</h2>";
echo "<pre>";
print_r($transformedData);
echo "</pre>";

// Validate.  Normally we'd want to halt on validation errors.  But for this demo, we will simply print the messages.
$validator = new \UserFrosting\Fortress\ServerSideValidator($schema, $translator);

if (!$validator->validate($transformedData)) {
    echo "<h2>Validation results</h2>";
    echo "<pre>";
    print_r($validator->errors());
    echo "</pre>";
}

// Test client validators
$clientVal = new \UserFrosting\Fortress\Adapter\JqueryValidationAdapter($schema, $translator);
echo "<h2>Client-side validation schema (JSON)</h2>";
echo "<pre>";
print_r($clientVal->rules());
echo "</pre>";

// If we've made it this far, success!
