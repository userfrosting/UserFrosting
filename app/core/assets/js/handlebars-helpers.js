/**
 * format an ISO date using Moment.js
 * http://momentjs.com/
 * moment syntax example: moment(Date("2011-07-18T15:50:52")).format("MMMM YYYY")
 * usage: {{dateFormat creation_date format="MMMM YYYY"}}
 */
Handlebars.registerHelper('dateFormat', function(context, block) {
  if (window.moment) {
    var f = block.hash.format || "MMM Do, YYYY";
    return moment(context).format(f);
  }else{
    return context;   //  moment plugin not available. return data as is.
  }
});

/**
 * Equality helper for Handlebars
 * http://stackoverflow.com/questions/8853396/logical-operator-in-a-handlebars-js-if-conditional/21915381#21915381
 * usage: {{ifCond apple orange}}
 */
Handlebars.registerHelper('ifCond', function(v1, v2, options) {
  if(v1 == v2) {
    return options.fn(this);
  }
  return options.inverse(this);
});
