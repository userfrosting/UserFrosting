/*

UserFrosting Version: 0.1
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

var APIPATH = getSitePath() + "api/";
var FORMSPATH = getSitePath() + "forms/";

// Returns the base URL of the website, assuming that this script is in the '/js' subdirectory of the site.
function getSitePath() {
    var scripts = document.getElementsByTagName('script'),
        script = scripts[scripts.length - 1];

    if (script.getAttribute.length !== undefined) {
		scriptPath = script.src;
    }

    scriptPath = script.getAttribute('src', -1);
	
	var basePath = scriptPath.substr(0, scriptPath.lastIndexOf( '/js' )+1 );
	console.log("base site path is: " + basePath);
	return basePath;
}

function getTemplateAjax(path) {
	var source;
	var template;
	$.ajax({
		url: path, //ex. js/templates/mytemplate.handlebars
		async: false,  
		success: function(data) {
			source    = data;
			template  = Handlebars.compile(source);
		}               
	});
	return template;
}

function formatCurrency(val) {
	var s = val.toString();
	if (s.indexOf('.') == -1) s += '.';
	while (s.length < s.indexOf('.') + 3) s += '0';
	return s;
}

function formatPhone(num) {
	num = num.replace(/[^0-9]/, '', num); 
	var len = num.length;
	if(len == 7)
	num = num.replace(/([0-9]{3})([0-9]{4})/, '$1-$2');
	else if(len == 10)
	num = num.replace(/([0-9]{3})([0-9]{3})([0-9]{4})/, '($1) $2-$3');
	return num;
}

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}

function isValidPhone(phone) {
	var pattern = new RegExp(/^[0-9\-\(\)\s]+/i);
	return pattern.test(phone);
}

function isValidDate(date) {
	var pattern = new RegExp(/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}/i);
	return pattern.test(date);
}

function isValidTime(time) {
	var pattern = new RegExp(/^[0-9]{1,2}:[0-9]{2} [am|pm]/i);
	return pattern.test(time);
}

// Converts SQL datetime of the form YYYY-MM-DD HH:MM:SS into milliseconds
// Assumes we are using the datejs extension to Date
function sqlDateToMilliseconds(sqlDateStr) {
	sqlDateStr = sqlDateStr.replace(/ /g,"T");
	return Date.parse(sqlDateStr).getTime();
}

// Convert milliseconds into a Javascript date object
function millisecondsToDate(sqlDateStr) {
	var msTime = sqlDateToMilliseconds(sqlDateStr);
	var dateTime = new Date();
	dateTime.setTime(msTime);
	return dateTime;
}

function formatDate1(stamp) {
	var formatted = {};
	formatted['stamp'] = stamp;
	var dateTime = new Date(stamp);
	formatted['date'] = dateTime.toString("MMM dS, yyyy");
	formatted['day'] = dateTime.toString("dddd");
	formatted['time'] = dateTime.toString("h:mm tt");
	return formatted;
}

function toTitleCase(str) {
    if (str.match(/^[A-Z]*$/)) {
		return str;
	} else {
		return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	}
}

// Get the value of a URI parameter from the current page by name.
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

// Find an object in an array of objects that has the corresponding field value
function findObjectByField(arr, field_value, field_name){
	field_name = typeof field_name !== 'undefined' ? field_name : "id";
	var item = null;
	jQuery.each(arr, function (a,b){
		if (b[field_name] == field_value){
			item = b;
		}
	});
	return item;
};

function getKeys(obj) {
	var keys = [];
	$.each( obj, function( key, value ) {
		keys.push(key);
	});
	return keys;
}

// Create an old-fashion html style select dropdown with typeahead capability
function typeaheadDropdown(typeahead_element, suggestions, suggestion_render_template, options) {
	var default_id = false;
	var disabled = false;
	if (options['default_id'])
		default_id = options['default_id'];
	if (options['disabled'])
		disabled = options['disabled'];

	var template = Handlebars.compile(suggestion_render_template);
	// Enable item
	//jQuery.fn._typeahead.noConflict();
	// Test array: var colors = ["red", "blue", "green", "yellow", "brown", "black"];    
	$(typeahead_element).typeahead({
		//name: classLabel,     // Update 2/19/2014: remove name to keep typeahead from caching data
		minLength: 0,
		limit: 100,
        highlight: true,
		local: suggestions,
		template: template,
		engine: Handlebars
	}).on('typeahead:closed', function (object, datum) {
		// Check to make sure that the inputted value is in the list of values.  If so, set the hidden -id field to the corresponding id
		var found = false;
		jQuery.each(suggestions, function(idx, item) {
			// Match the item name against the suggestions list
			if (item['name'].toLowerCase() == $(typeahead_element).val().toLowerCase())
				found = item;
		});
		if (found ) {
			// Set the selected hidden id and trigger the "change" event
			var selected_id = found['id'];
			$(typeahead_element).data("selected_id", selected_id).trigger('change');
		} else {
			$(typeahead_element).typeahead('setQuery', "");
		}

	});

	// Set default value if specified
	if (default_id) {
		// Look for name
		var found = false;
		jQuery.each(suggestions, function(idx, item) {
			// Match the item name against the suggestions list
			if (item['id'] == default_id)
				found = item;
		});
		if (found ) {
			// Set the selected hidden id and trigger the "change" event
			var selected_id = found['id'];
			$(typeahead_element).typeahead('setQuery', found['name']);
			$(typeahead_element).data("selected_id", selected_id).trigger('change');
		} else {
			$(typeahead_element).typeahead('setQuery', "");
		}
	}
	// Disable if specified
	if (disabled) {
		$(typeahead_element).typeahead('destroy');
		$(typeahead_element).prop('disabled', true);
	}
}

function validateFormFields(dialog_id) {
	// Check all input fields
	var errorMessages = [];
	var $fields = $('#' + dialog_id + ' .form-control');
	$fields.each(function(idx, input) {
		// Get handle for closest input-group indicator
		var closestGroup = $( this ).closest('.input-group');
		// Get field value
		var val = "";
		// Check that radio box has one option selected
		if ($( this ).attr('type') == "radio") {
			var checkedOption = $('input[name="' + input.name + '"]' ).filter(':checked');
			if (checkedOption.length != 0)
				val = checkedOption.val();
			console.log(val);
		} else {
			val = input.value;
		}
		console.log("validating field: " + input.name + "='" + val + "'");		
		var fieldErrors = 0;
		// Get validation metadata
		var validationData = $(this).data('validate');
		if (validationData){
			// If no field label specified, use a generic label
			var label = "One of the fields";
			if (validationData.label) {
				label = validationData.label;
			}
			// Skip blank, optional fields
			if (!(validationData.optional && val == "")) {
				if (validationData.minLength && validationData.maxLength) {
					console.log("validating for length " + val.length);
					if (val.length < validationData.minLength || val.length > validationData.maxLength) {
						errorMessages.push("'" + label + "' must be between " + validationData.minLength + " and " + validationData.maxLength + " characters long.");
						fieldErrors += 1;
					}
				}
				if (validationData.minLength && !validationData.maxLength) {
					if (val.length < validationData.minLength) {
						errorMessages.push("'" + label + "' must be at least " + validationData.minLength + " characters long.");
						fieldErrors += 1;
					}
				}		
				if (!validationData.minLength && validationData.maxLength) {
					if (val.length > validationData.maxLength) {
						errorMessages.push("'" + label + "' must be at most " + validationData.maxLength + " characters long.");
						fieldErrors += 1;
					}
				}
				if (validationData.email) {
					if (!isValidEmailAddress(val)) {
						errorMessages.push("'" + label + "' must be a valid email address.");
						fieldErrors += 1;
					}
				}
				if (validationData.number) {
					if (isNaN(val) || val == "") {
						errorMessages.push("'" + label + "' must be a number.");
						fieldErrors += 1;
					}
				}
				if (validationData.phone) {
					if (!isValidPhone(val)) {
						errorMessages.push("'" + label + "' must be a valid phone number.");
						fieldErrors += 1;
					}
				}
				if (validationData.date) {
					if (!isValidDate(val)) {
						errorMessages.push("'" + label + "' must be a date in the format mm/dd/yyyy.");
						fieldErrors += 1;
					}
				}
				if (validationData.time) {
					if (!isValidTime(val)) {
						errorMessages.push("'" + label + "' must be a time in the format h:mm am or h:mm pm.");
						fieldErrors += 1;
					}
				}
				if (validationData.selected) {
					var msg = "You must select an option for '" + label + "'.";
					if (val == "") {
						if ($.inArray(msg, errorMessages) == -1) {
							errorMessages.push(msg);
						}
						fieldErrors += 1;
					}
				}
				if (validationData.passwordMatch) {
					var other = $('#' + dialog_id + ' input[name="' + validationData.passwordMatch + '"]');
					if (val != other.val()) {
						errorMessages.push("Passwords must match!");
						fieldErrors += 1;
					}
				}
			}
		}
		if (fieldErrors > 0) {
			closestGroup.addClass('has-error');
			closestGroup.removeClass('has-success');
		} else {
			closestGroup.removeClass('has-error');
			closestGroup.addClass('has-success');
		}		
	});
	//console.log(errorMessages);
	return errorMessages;
}

function loadCurrentUser() {
	var url = APIPATH + 'load_current_user.php';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false
	}).responseText;	
	var resultJSON = processJSONResult(result);
	
	if (resultJSON['user_id']) {
		return resultJSON;
	} else {
		addAlert("danger", "We couldn't load your account. We'll try to get this fixed right away!");
		window.location.replace('logout.php');
		return;
	}
}

// Load permissions for the logged in user
function userLoadPermissions() {
	var url = APIPATH + 'load_groups.php';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false,
	  data: {user_id: '0'}
	}).responseText;
	var resultJSON = processJSONResult(result);
	return resultJSON;
}

function loadAllGroups() {
	var url = APIPATH + 'load_groups.php';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false,
	  data: {}
	}).responseText;
	var resultJSON = processJSONResult(result);
	return resultJSON;
}

function loadSecureFunctions() {
	var url = APIPATH + 'load_secure_functions.php';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false,
	  data: {}
	}).responseText;
	var resultJSON = processJSONResult(result);
	return resultJSON;
}

function loadPermissionValidators() {
	var url = APIPATH + 'load_permission_validators.php';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false,
	  data: {}
	}).responseText;
	var resultJSON = processJSONResult(result);
	return resultJSON;
}

function addAlert(type, msg) {
	var url = APIPATH + 'user_alerts.php';
	$.ajax({  
	  type: "POST",  
	  url: url,
	  async: false,
	  data: {
		type: type,
		message: msg
	  },		  
	  success: function() {
		//alert("Added alert successfully");
		// Do nothing.  What, you want an alert that your alert posted successfully?
	  }
	}); 
}

// Load alerts from $_SESSION['userAlerts'] variable into specified element
function alertWidget(widget_id){
	var url = APIPATH + 'user_alerts.php';
	$.getJSON( url, {})
	.done(function( data ) {
		var alertHTML = "";
		jQuery.each(data, function(alert_idx, alert_message) {
			if (alert_message['type'] == "success"){
				alertHTML += "<div class='alert alert-success'>" + alert_message['message'] + "</div>";
			} else if (alert_message['type'] == "warning"){
				alertHTML += "<div class='alert alert-warning'>" + alert_message['message'] + "</div>";
			} else 	if (alert_message['type'] == "info"){
				alertHTML += "<div class='alert alert-info'>" + alert_message['message'] + "</div>";
			} else if (alert_message['type'] == "danger"){
				alertHTML += "<div class='alert alert-danger'>" + alert_message['message'] + "</div>";
			}
		});	
		$('#' + widget_id).html(alertHTML);
		$("html, body").animate({ scrollTop: 0 }, "fast");		// Scroll back to top of page
	});
}

function processJSONResult(result) {
	if (!result) {
		addAlert("danger", "Oops, this feature doesn't seem to be working at the moment.  Totally our bad.");
		return {"errors": 1, "successes": 0};
	} else {
		try {
			if (typeof result == 'string') {
				return jQuery.parseJSON(result);
			} else {
				return result;
			}
		} catch (err) {
			console.log("Backend error: " + result);
			addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
			return {"errors": 1, "successes": 0};
		}	
	}
}
