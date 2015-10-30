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
