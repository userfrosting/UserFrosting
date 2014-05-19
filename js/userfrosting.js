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

// Create an old-fashion html style select dropdown with typeahead capability
function presetDropdown(dialog_id, classLabel, suggestions, default_id, disabled) {
	default_id = typeof default_id !== 'undefined' ? default_id : "";
	disabled = typeof disabled !== 'undefined' ? disabled : false;
	
	var source = "<p>{{name}}</p>";
	var template = Handlebars.compile(source);
	// Enable item
	//jQuery.fn._typeahead.noConflict();
	// Test array: var colors = ["red", "blue", "green", "yellow", "brown", "black"];     
	$('#' + dialog_id + ' .' + classLabel).typeahead({
		//name: classLabel,     // Update 2/19/2014: remove name to keep typeahead from caching data
		minLength: 0,
		limit: 100,
		local: suggestions,
		template: template,
		engine: Handlebars
	}).on('typeahead:closed', function (object, datum) {
		console.log("Typeahead updated.");
		// Check to make sure that the inputted value is in the list of values.  If so, set the hidden -id field to the corresponding id
		var found = false;
		jQuery.each(suggestions, function(idx, item) {
			// Match the item name against the suggestions list
			console.log("Checking item " + item['name']);
			if (item['name'].toLowerCase() == $('#' + dialog_id + ' .' + classLabel).val().toLowerCase())
				found = item;
		});
		if (found ) {
			// Set the selected hidden id and trigger the "change" event
			var selected_id = found['id'];
			console.log("FIRING TRIGGER: " + selected_id);
			$('#' + dialog_id + ' .' + classLabel + '-id' ).val(selected_id).trigger('change');

		} else {
			$('#' + dialog_id + ' .' + classLabel).typeahead('setQuery', "");
			console.log("Item not found in list.");
		}

	});
	// Set default value if specified
	if (default_id) {
		console.log("Default value is " + default_id);
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
			$('#' + dialog_id + ' .' + classLabel).typeahead('setQuery', found['name']);
			console.log("FIRING TRIGGER: " + selected_id);
			$('#' + dialog_id + ' .' + classLabel + '-id' ).val(selected_id).trigger('change');
		} else {
			$('#' + dialog_id + ' .' + classLabel).typeahead('setQuery', "");
			console.log("Item not found in list.");
		}
	}
	// Disable if specified
	if (disabled) {
		console.log("Disabling typeahead.");
		$('#' + dialog_id + ' .' + classLabel).typeahead('destroy');
		$('#' + dialog_id + ' .' + classLabel).prop('disabled', true);
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
	console.log(errorMessages);
	return errorMessages;
}

function loadCurrentUser() {
	var url = 'load_current_user.php';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false
	}).responseText;	
	var resultJSON = processJSONResult(result);
	
	if (resultJSON['id']) {
		return resultJSON;
	} else {
		addAlert("danger", "We couldn't load your account. We'll try to get this fixed right away!");
		window.location.replace('404.php');
		return;
	}
}

function loadPermissions(div_id) {
  var url = "load_permissions.php";
  $.getJSON( url, {})
  .done(function( data ) {		  
	if (Object.keys(data).length > 0) { // Don't bother unless there are some records found
	  jQuery.each(data, function(idx, record) {
		$('#' + div_id).append("<li class='list-group-item'>" + record['name'] + 
		"<button class='btn btn-sm btn-danger deletePermission pull-right' data-id='" + record['id'] + "'>Delete</button></li>");
	  });
	  
	  // Bind delete buttons
	  $('.deletePermission').on('click', function(){
		var btn = $(this);
		var id = btn.data('id');
		deletePermission(id);
	  });
	}
  });
}

function addNewPermission(permission_name) {
  var url = 'create_permission.php';
  $.ajax({  
	type: "POST",  
	url: url,  
	data: {
	  new_permission:		permission_name,
	  ajaxMode:				"true"
	}		  
  }).done( function(result) {
	var resultJSON = processJSONResult(result);
	alertWidget('display-alerts');
	if (resultJSON['errors'] == 0) {
		// If no errors, reload the newly added permission as a new element in the list
		$('#permission-groups').html("");
		loadPermissions('permission-groups');
	}
  });
}

function deletePermission(id) {
  var url = 'delete_permission.php';
  $.ajax({  
	type: "POST",  
	url: url,  
	data: {
	  permission_id:		id,
	  ajaxMode:				"true"
	}
  }).done( function(result) {
	var resultJSON = processJSONResult(result);
	alertWidget('display-alerts');
	if (resultJSON['errors'] == 0) { 
		// If no errors, reload the permissions list
		$('#permission-groups').html("");
		loadPermissions('permission-groups');
	}
  });		  
}

// Load permissions for the logged in user
function userLoadPermissions() {
	var url = 'user_load_permissions.php';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false
	}).responseText;
	var resultJSON = processJSONResult(result);
	return resultJSON;
}

// Load permissions for a specified user in admin mode
function adminLoadPermissions(user_id) {
	var url = 'admin_load_permissions.php';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false,
	  data: {
		user_id: user_id
	  }
	}).responseText;
	var resultJSON = processJSONResult(result);
	return resultJSON;
}

function loadAllPermissions() {
	var url = 'load_permissions.php';
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
	var url = 'UserFrosting/app/controllers/pages/user_alerts.php';
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
	var url = 'app/controllers/pages/user_alerts.php';
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
