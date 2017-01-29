/**
 * uf-form plugin.  Handles validation and submission for basic UserFrosting forms.
 *
 * This plugin uses the jQueryvalidation plugin (https://jqueryvalidation.org/) to perform instant, client-side form validation.
 * UserFrosting forms must be wrapped in a <form> element, and contain a <button type=submit> element for submission.
 *
 * Forms are then set to submit via AJAX when the submit button is clicked.
 *
 * === USAGE ===
 *
 * uf-form can be initialized on a form element as follows:
 *
 * $("#myForm").ufForm(options);
 *
 * `options` is an object containing any of the following parameters:
 * @param {JSON} validators An object containing two keys, "rules" and "messages", which specify the jQueryvalidation rules to use.
 * @param {Object} msgTarget a jQuery selector specifying the element where any error messages should be inserted.  Defaults to looking for a container with class .js-form-alerts inside this form.
 * @param {Callback} beforeSubmitCallback a callback function to execute immediately after form validation, before the form is submitted.
 * @param {bool} binaryCheckboxes specify whether to submit checkboxes as binary values 0 and 1, instead of omitting unchecked checkboxes from submission.
 *
 * == EVENTS ==
 *
 * ufForm triggers the following events:
 *
 * `submitSuccess.ufForm`: triggered when the form is successfully submitted, after re-enabling the submit button.
 * `submitError.ufForm`: triggered when the form submission (not validation) fails, after re-enabling the submit button
 * and displaying any error messages.
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman https://alexanderweissman.com
 */

(function( $ )
{
    /**
     * The plugin namespace, ie for $('.selector').ufForm(options)
     *
     * Also the id for storing the object state via $('.selector').data()
     */
    var PLUGIN_NS = 'ufForm';

    var Plugin = function ( target, options )
    {
        this.$T = $(target);

        /** #### OPTIONS #### */
        this.options= $.extend(
            true,               // deep extend
            {
                validators          : {
                    'rules'   : {},
                    'messages': {}
                },
                msgTarget           : this.$T.find('.js-form-alerts'),
                beforeSubmitCallback: null,
                binaryCheckboxes    : true,     // submit checked/unchecked checkboxes as 0/1 values
                DEBUG: false
            },
            options
        );

        this._init( target, options );

        return this;
    }

    /** #### INITIALISER #### */
    Plugin.prototype._init = function ( target, options )
    {
        var base = this;
        var $el = $(target);

        var validator = $el.validate({
            rules:          base.options.validators.rules,
            messages :      base.options.validators.messages,
            submitHandler:  function (f, e) {
                // Execute any "before submit" callback
                if (base.options.beforeSubmitCallback)
                    base.options.beforeSubmitCallback();

                var form = $(f);

                // Set "loading" text for submit button, if it exists, and disable button
                var submit_button = form.find("button[type=submit]");
                if (submit_button) {
                    var submit_button_text = submit_button.html();
                    submit_button.prop( "disabled", true );
                    submit_button.html("<i class='fa fa-spinner fa-spin'></i>");
                }

                // Serialize and post to the backend script in ajax mode
                if (base.options.binaryCheckboxes) {
                    var serializedData = form.find(':input').not(':checkbox').serialize();
                    // Get unchecked checkbox values, set them to 0
                    form.find('input[type=checkbox]:enabled').each(function() {
                        if ($(this).is(':checked'))
                            serializedData += "&" + encodeURIComponent(this.name) + "=1";
                        else
                            serializedData += "&" + encodeURIComponent(this.name) + "=0";
                    });
                } else {
                    var serializedData = form.find(':input').serialize();
                }

                // Submit the form via AJAX
                var url = form.attr('action');
                $.ajax({
                  type: form.attr('method'),
                  url: url,
                  data: serializedData
                })
                .then(
                    // Submission successful
                    function (data, textStatus, jqXHR) {
                        // Restore button text and re-enable submit button
                        if (submit_button) {
                            submit_button.prop( "disabled", false );
                            submit_button.html(submit_button_text);
                        }

                        base.$T.trigger('submitSuccess.ufForm', [data, textStatus, jqXHR]);
                        return jqXHR;
                    },
                    // Submission failed
                    function (jqXHR, textStatus, errorThrown) {
                        // Restore button text and re-enable submit button
                        if (submit_button) {
                            submit_button.prop( "disabled", false );
                            submit_button.html(submit_button_text);
                        }
                        // Error messages
                        if ((typeof site !== "undefined") && site.debug.ajax && jqXHR.responseText) {
                            base.$T.trigger('submitError.ufForm', [jqXHR, textStatus, errorThrown]);
                            document.write(jqXHR.responseText);
                            document.close();
                        } else {
                            if (base.options.DEBUG) {
                                console.log("Error (" + jqXHR.status + "): " + jqXHR.responseText );
                            }
                            // Display errors on failure
                            // TODO: ufAlerts widget should have a 'destroy' method
                            if (!base.options.msgTarget.data('ufAlerts')) {
                                base.options.msgTarget.ufAlerts();
                            } else {
                                base.options.msgTarget.ufAlerts('clear');
                            }

                            base.options.msgTarget.ufAlerts('fetch').ufAlerts('render');
                            base.options.msgTarget.on("render.ufAlerts", function () {
                                base.$T.trigger('submitError.ufForm', [jqXHR, textStatus, errorThrown]);
                            });
                        }
                        return jqXHR;
                    }
                );
            }
        });
    };

    /**
     * EZ Logging/Warning (technically private but saving an '_' is worth it imo)
     */
    Plugin.prototype.DLOG = function ()
    {
        if (!this.DEBUG) return;
        for (var i in arguments) {
            console.log( PLUGIN_NS + ': ', arguments[i] );
        }
    }
    Plugin.prototype.DWARN = function ()
    {
        this.DEBUG && console.warn( arguments );
    }


/*###################################################################################
 * JQUERY HOOK
 ###################################################################################*/

    /**
     * Generic jQuery plugin instantiation method call logic
     *
     * Method options are stored via jQuery's data() method in the relevant element(s)
     * Notice, myActionMethod mustn't start with an underscore (_) as this is used to
     * indicate private methods on the PLUGIN class.
     */
    $.fn[ PLUGIN_NS ] = function( methodOrOptions )
    {
        if (!$(this).length) {
            return $(this);
        }
        var instance = $(this).data(PLUGIN_NS);

        // CASE: action method (public method on PLUGIN class)
        if ( instance
                && methodOrOptions.indexOf('_') != 0
                && instance[ methodOrOptions ]
                && typeof( instance[ methodOrOptions ] ) == 'function' ) {

            return instance[ methodOrOptions ]( Array.prototype.slice.call( arguments, 1 ) );


        // CASE: argument is options object or empty = initialise
        } else if ( typeof methodOrOptions === 'object' || ! methodOrOptions ) {

            instance = new Plugin( $(this), methodOrOptions );    // ok to overwrite if this is a re-init
            $(this).data( PLUGIN_NS, instance );
            return $(this);

        // CASE: method called before init
        } else if ( !instance ) {
            $.error( 'Plugin must be initialised before using method: ' + methodOrOptions );

        // CASE: invalid method
        } else if ( methodOrOptions.indexOf('_') == 0 ) {
            $.error( 'Method ' +  methodOrOptions + ' is private!' );
        } else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery);
