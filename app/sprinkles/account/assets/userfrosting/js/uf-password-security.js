$(document).ready(function() {

    var breachThreshold = site.password.security.enforce_no_compromised.breaches;

    // Only perform security checks if they are enabled
    if (breachThreshold != -1) {
        // Setup before functions
        var passwordInput = $('form').find('input[name=password]');
        var typingTimer; // Timer identifier
        var doneTypingInterval = 400; // Time in ms

        /**
         * Checks if password is valid according to site policies.
         */
        function triggerPasswordValidation() {
            var ajaxData = {};
            ajaxData['password'] = $(passwordInput).val();
            ajaxData[site.csrf.keys.name] = site.csrf.name;
            ajaxData[site.csrf.keys.value] = site.csrf.value;
            $.ajax({
                type: 'POST',
                url: site.uri.public + '/account/check-password',
                data: ajaxData,
                success: function(data) {

                    $(passwordInput).rules('add', {
                        'check-password': data
                    });

                    $(passwordInput).valid();
                }
            });
        }

        // On keyup, start the countdown
        passwordInput.keyup(function() {
            clearTimeout(typingTimer);
            if (passwordInput.val()) {
                typingTimer = setTimeout(triggerPasswordValidation, doneTypingInterval);
            }
        });


        jQuery.validator.addMethod(
            'check-password',
            function(value, element, breaches) {
                return breaches <= breachThreshold;
            },
            jQuery.validator.format('The password security policy does not allow use of passwords that have been exposed by a prior data breach. Please enter a different password.')
        );

    }
});