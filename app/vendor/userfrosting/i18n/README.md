# I18n module for UserFrosting

Alexander Weissman, 2016

The I18n module handles translation tasks for UserFrosting.  The `MessageTranslator` class can be used as follows:

## Step 1 - Set up language file(s).

A language file returns an array mapping message tokens to messages.  Messages may optionally have placeholders.  For example:

**locale/es_ES.php**

```
return array(
	"ACCOUNT_SPECIFY_USERNAME" => "Introduce tu nombre de usuario.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Introduce tu nombre público.",
	"ACCOUNT_USER_CHAR_LIMIT" => "Tu nombre de usuario debe estar entre {{min}} y {{max}} caracteres de longitud."
);
```

**locale/en_US.php**

```
return array(
	"ACCOUNT_SPECIFY_USERNAME" => "Please enter your user name.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Please enter your display name.",
	"ACCOUNT_USER_CHAR_LIMIT" => "Your user name must be between {{min}} and {{max}} characters in length."
);
```

## Step 2 - Set up translator object

```
$translator = new \UserFrosting\I18n\MessageTranslator();
$translator->setTranslationTable("locale/es_ES.php");
$translator->setDefaultTable("locale/en_US.php");
```

## Step 3 - Do a translation!

```
echo $translator->translate("ACCOUNT_USER_CHAR_LIMIT", [
    "min" => 4,
    "max" => 200
]);

// Returns "Tu nombre de usuario debe estar entre 4 y 200 caracteres de longitud."
```
