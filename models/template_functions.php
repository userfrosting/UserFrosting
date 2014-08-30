<?php

function renderAccountPageHeader($hooks = array()){
    global $master_account;
	if(isset($_SESSION["userCakeUser"]) && is_object($_SESSION["userCakeUser"]) and $_SESSION["userCakeUser"]->user_id == $master_account){
        $hooks["#SB_STYLE#"] = 'sb-admin-master.css';
    } else {
        $hooks["#SB_STYLE#"] = 'sb-admin.css';
    }
	
	return renderTemplate(ACCOUNT_HEAD_FILE, $hooks);	 
}


// TODO: clear unspecified placeholders
function renderTemplate($template_file, $hooks = array()){    
	$contents = file_get_contents(SITE_ROOT . "models/page-templates/" . $template_file);
    
    //Check to see we can access the file / it has some contents
    if(!$contents || empty($contents)) {
          addAlert("danger", "One or more templates for this page is missing.");
          return null;
    } else { 
        $find = array_keys($hooks);
        $replace = array_values($hooks);
        
        //Replace hooks
        $contents = str_replace($find, $replace, $contents);
        
        return $contents;
    }
}

function replaceKeyHooks($data, $template){
    foreach ($data as $key => $value){
        if (gettype($value) != "array" && gettype($value) != "object") {
            $find = '{{' . $key . '}}';
            $template = str_replace($find, $value, $template);
        }
    }
    return $template;
}

?>
