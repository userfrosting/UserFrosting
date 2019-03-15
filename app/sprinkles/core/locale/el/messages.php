<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 *
 * Greek message token translations for the 'core' sprinkle.
 *
 * @package userfrosting\i18n\el
 * @author Lena Stergatou
 */
 
return [
    "@PLURAL_RULE" => 1,
    "ABOUT" => "Σχετικά",
    "CAPTCHA" => [
        "@TRANSLATION" => "Captcha",
        "FAIL" => "Δεν έχετε εισάγει σωστά τον κωδικό captcha.",
        "SPECIFY" => "Εισαγάγετε τον captcha",
        "VERIFY" => "Επαλήθευση του captcha"
    ],
    "CSRF_MISSING" => "Λείπει το CSRF token. Γιατί δεν δοκιμάζετε να ανανεώσετε τη σελίδα και στη συνέχεια να υποβάλετε ξανά;",
    "DB_INVALID" => "Δεν είναι δυνατή η σύνδεση με τη βάση δεδομένων Εάν είστε διαχειριστής, ελέγξτε το αρχείο καταγραφής σφαλμάτων.",
    "DESCRIPTION" => "Περιγραφή",
    "DOWNLOAD" => [
        "@TRANSLATION" => "Λήψη",
        "CSV" => "Λήψη CSV"
    ],
    "EMAIL" => [
        "@TRANSLATION" => "Email",
        "YOUR" => "Η διεύθυνσή σας ηλεκτρονικού ταχυδρομείου"
    ],
    "HOME" => "Αρχική σελίδα",
    "LEGAL" => [
        "@TRANSLATION" => "Όροι χρήσης",
        "DESCRIPTION" => "Οι όροι χρήσης που ισχύουν για τη χρήση αυτού του ιστότοπου και των υπηρεσιών μας."
    ],
    "LOCALE" => [
        "@TRANSLATION" => "Γλώσσα"
    ],
    "NAME" => "Όνομα",
    "NAVIGATION" => "Πλοήγηση",
    "NO_RESULTS" => "Λυπούμαστε, δεν έχουμε τίποτα εδώ",
    "PAGINATION" => [
        "GOTO" => "Μετάβαση στη σελίδα",
        "SHOW" => "Εμφάνιση", "OUTPUT" => "{startRow} έως {endRow} από {filteredRows} ({totalRows})",
        // Paginator
        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
        "OUTPUT" => "{startRow} έως {endRow} από {filteredRows} ({totalRows})",
        "NEXT" => "Επόμενη σελίδα",
        "PREVIOUS" => "Προηγούμενη σελίδα",
        "FIRST" => "Πρώτη σελίδα",
        "LAST" => "Τελευταία σελίδα"
    ],
    "PRIVACY" => [
        "@TRANSLATION" => "Πολιτική απορρήτου",
        "DESCRIPTION" => "Η πολιτική απορρήτου μας περιγράφει τι είδους πληροφορίες συλλέγουμε από εσάς και πώς θα το χρησιμοποιήσουμε" .
            ],
    "SLUG" => "Slug",
    "SLUG_CONDITION" => "Slug/ Προϋποθέσεις",
    "SLUG_IN_USE" => "Υπάρχει ήδη ένα slug <strong> {{slug}} </ strong>",
    "STATUS" => "Κατάσταση",
    "SUGGEST" => "Προτείνετε",
    "UNKNOWN" => "Άγνωστο",
// Actions words
    "ACTIONS" => "Ενέργειες",
    "ACTIVATE" => "Ενεργοποίηση",
    "ACTIVE" => "Ενεργό",
    "ADD" => "Προσθήκη",
    "CANCEL" => "Ακύρωση",
    "CONFIRM" => "Επιβεβαίωση",
    "CREATE" => "Δημιουργία",
    "DELETE" => "Διαγραφή",
    "DELETE_CONFIRM" => "Είστε βέβαιοι ότι θέλετε να γίνει η διαγραφή;",
    "DELETE_CONFIRM_YES" => "Ναι, διαγραφή",
    "DELETE_CONFIRM_NAMED" => "Είστε βέβαιοι ότι θέλετε να διαγράψετε το {{name}};",
    "DELETE_CONFIRM_YES_NAMED" => "Ναι, διαγραφή του {{name}}",
    "DELETE_CANNOT_UNDONE" => "Αυτή η ενέργεια δεν μπορεί να ακυρωθεί.",
    "DELETE_NAMED" => "Διαγραφή {{name}}",
    "DENY" => "Άρνηση",
    "DISABLE" => "Απενεργοποίηση",
    "DISABLED" => "Απενεργοποιημένο",
    "EDIT" => "Επεξεργασία",
    "ENABLE" => "Ενεργοποίηση",
    "ENABLED" => "Ενεργοποιημένο",
    "OVERRIDE" => "Παράκαμψη",
    "RESET" => "Επαναφορά",
    "SAVE" => "Αποθήκευση",
    "ΑΝΑΖΗΤΗΣΗ" => "Αναζήτηση",
    "SORT" => "Ταξινόμηση",
    "SUBMIT" => "Υποβολή",
    "PRINT" => "Εκτύπωση",
    "REMOVE" => "Κατάργηση",
    "UNACTIVATED" => "Απενεργοποιημένο",
    "UPDATE" => "Ενημέρωση",
    "YES" => "Ναι",
    "NO" => "Όχι",
    "OPTIONAL" => "Προαιρετικό",
// Misc.
    "BUILT_WITH_UF" => "Κατασκευάστηκε με το <a href=\"http://www.userfrosting.com\">UserFrosting </a>",
    "ADMINLTE_THEME_BY" => "Θέμα από <strong> <a href=\"http://almsaeedstudio.com\>> Almsaeed Studio </a>. </ Strong> Όλα τα δικαιώματα διατηρούνται",
    "WELCOME_TO" => "Καλώς ορίσατε στο {{title}}!"
];
