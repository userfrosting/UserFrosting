// Initialize bootstrap switches, if enabled
if (jQuery().bootstrapSwitch){
    $('.bootstrapswitch').bootstrapSwitch();
} else {
    console.log("The bootstrap-switch plugin has not been added.");
}

// Initialize select2 dropdowns, if enabled
if (jQuery().select2){
    $('.select2').select2();
} else {
    console.log("The select2 plugin has not been added.");
}
