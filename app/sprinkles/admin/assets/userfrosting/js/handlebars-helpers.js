/**
 * This file contains extra helper functions for Handlebars.js.
 *
 * @see http://handlebarsjs.com/#helpers
 */

 /**
  * Improved comparison operator
  * See https://stackoverflow.com/a/16315366/2970321
  */
Handlebars.registerHelper('ifx', function (v1, operator, v2, options) {
    switch (operator) {
        case '==':
            return (v1 == v2) ? options.fn(this) : options.inverse(this);
        case '===':
            return (v1 === v2) ? options.fn(this) : options.inverse(this);
        case '!=':
            return (v1 != v2) ? options.fn(this) : options.inverse(this);
        case '!==':
            return (v1 !== v2) ? options.fn(this) : options.inverse(this);
        case '<':
            return (v1 < v2) ? options.fn(this) : options.inverse(this);
        case '<=':
            return (v1 <= v2) ? options.fn(this) : options.inverse(this);
        case '>':
            return (v1 > v2) ? options.fn(this) : options.inverse(this);
        case '>=':
            return (v1 >= v2) ? options.fn(this) : options.inverse(this);
        case '&&':
            return (v1 && v2) ? options.fn(this) : options.inverse(this);
        case '||':
            return (v1 || v2) ? options.fn(this) : options.inverse(this);
        default:
            return (v1 == v2) ? options.fn(this) : options.inverse(this);
    }
});

/**
 * Perform simple calculations.
 *
 * usage: {{calc x '+' 2}}
 */
Handlebars.registerHelper('calc', function (v1, operator, v2, options) {
    lvalue = parseFloat(v1);
    rvalue = parseFloat(v2);

    return {
        "+": lvalue + rvalue,
        "-": lvalue - rvalue,
        "*": lvalue * rvalue,
        "/": lvalue / rvalue,
        "%": lvalue % rvalue
    }[operator];
});

/**
 * format an ISO date using Moment.js
 *
 * moment syntax example: moment(Date("2011-07-18T15:50:52")).format("MMMM YYYY")
 * usage: {{dateFormat creation_date format="MMMM YYYY"}}
 * @requires momentjs http://momentjs.com/
 */
Handlebars.registerHelper('dateFormat', function(context, block) {
    if (window.moment) {
        var f = block.hash.format || "MMM Do, YYYY";
        return moment(context).format(f);
    } else {
        //  moment plugin not available. return data as is.
        console.log("The moment.js plugin is not loaded.  Please make sure you have included moment.js on this page.");
        return context;
    }
});

/**
 * Format a phone number.
 */
Handlebars.registerHelper("phoneUSFormat", function(phoneNumber) {
  if (typeof phoneNumber === 'undefined') {
    return '';
  }

  phoneNumber = phoneNumber.toString();
  return "(" + phoneNumber.substr(0,3) + ") " + phoneNumber.substr(3,3) + "-" + phoneNumber.substr(6,4);
});

/**
 * Format currency (USD).
 */
Handlebars.registerHelper("currencyUsdFormat", function(amount) {
    var parsedAmount = parseFloat(amount);
    if (parsedAmount < 0) {
        return "-$" + Math.abs(parsedAmount).toFixed(2);
    } else {
        return "$" + parsedAmount.toFixed(2);
    }
});

/**
 * Convert a string to a slug using speakingurl.js.
 *
 * @requires speakingurl https://pid.github.io/speakingurl/
 */
Handlebars.registerHelper('slug', function(text) {
    return getSlug(text);
});
