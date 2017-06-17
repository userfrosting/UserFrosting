$.validator.addMethod("equals", function(value, element, params) {
    params = $.extend(
        true,               // deep extend
        {
            value: '',
            caseSensitive: false
        }, params);

    if (!params.caseSensitive) {
        params.value = params.value.toLowerCase();
        value = value.toLowerCase();
    }
	return this.optional(element) || value == params.value;
}, "Value is not correct.");

$.validator.addMethod("notEquals", function(value, element, params) {
    params = $.extend(
        true,               // deep extend
        {
            value: '',
            caseSensitive: false
        }, params);

    if (!params.caseSensitive) {
        params.value = params.value.toLowerCase();
        value = value.toLowerCase();
    }
	return this.optional(element) || value != params.value;
}, "Value is not correct.");

$.validator.addMethod("noLeadingWhitespace", function(value, element) {
	return this.optional(element) || /^\S.*$/i.test(value);
}, "No leading whitespace allowed");

$.validator.addMethod("noTrailingWhitespace", function(value, element) {
	return this.optional(element) || /^.*\S$/i.test(value);
}, "No trailing whitespace allowed");

jQuery.validator.addMethod("memberOf", function(value, element, arr) {
    return $.inArray(value, arr) != -1;
}, "Data provided must match one of the provided options.");

jQuery.validator.addMethod("notMemberOf", function(value, element, arr) {
    return $.inArray(value, arr) == -1;
}, "Data provided must NOT match one of the provided options.");

jQuery.validator.addMethod("matchFormField", function(value, element, field) {
    return value === $(element).closest('form').find("input[name=" + field + "]").val();
}, "The specified fields must match.");

jQuery.validator.addMethod("notMatchFormField", function(value, element, field) {
    return value !== $(element).closest('form').find("input[name=" + field + "]").val();
}, "The specified fields must NOT match.");

$.validator.addMethod("username", function(value, element) {
	return this.optional(element) || /^([a-z0-9\.\-_])+$/.test(value);
}, "The field may only contain lowercase letters, digits, '.', '-', and '_'.");
