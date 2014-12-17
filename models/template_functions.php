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

function renderMenu($highlighted_item_class){
    // User must be logged in
    if (!isUserLoggedIn()){
      addAlert("danger", lang("LOGIN_REQUIRED"));
      apiReturnError(false, SITE_ROOT . "login.php");
    }
    
    global $loggedInUser, $master_account;
    
    $hooks = array(
              "#USERNAME#" => $loggedInUser->username,
              "#WEBSITENAME#" => SITE_TITLE
              );
    
    // Special case for root account
    if ($loggedInUser->user_id == $master_account){
        $hooks['#HEADERMESSAGE#'] = "<span class='navbar-center navbar-brand'>YOU ARE CURRENTLY LOGGED IN AS ROOT USER</span>";
    } else {
        $hooks['#HEADERMESSAGE#'] = "";
    }

    $menu = fetchMenu($loggedInUser->user_id);

    $html = '
    <!-- Brand and toggle get grouped for better mobile display -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="../account/index.php">#WEBSITENAME#</a>
    #HEADERMESSAGE#
</div>

<div class="collapse navbar-collapse navbar-ex1-collapse">
    <!-- Collect the nav links, forms, and other content for toggling -->
    <ul class="nav navbar-nav side-nav">';
    foreach ($menu as $r => $v){
        // Set active class if this item is currently selected
        $active = ($highlighted_item_class == $v['class_name']) ? "active" : "";
    
        if ($v['menu'] == 'left' AND $v['menu'] != 'left-sub'){
            $html .= "<li class='navitem-".$v['class_name']." $active'><a href='../".$v['page']."'><i class='".$v['icon']."'></i> ".$v['name']."</a></li>";
        }
        if ($v['menu'] == 'left-sub' AND $v['parent_id'] == 0){
            $html .= "<li class='dropdown'>
                <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='".$v['icon']."'></i> ".$v['name']." <b class='caret'></b></a>
                <ul class='dropdown-menu'>";
            // Grab submenu items based on parent_id = $v['menu_id']
            $subs = gatherSubMenuItems($v['menu_id']);

            // If subs are found print them out to the parent element
            foreach ($subs as $s){
                $html .= "<li class='navitem-".$s['class_name']."'><a href='../".$s['page']."'><i class='".$s['icon']."'></i> ".$s['name']."</a></li>";
            }
            $html .= '</ul></li>';
        }
    }
    $html .= '</ul>';
//top nav bar
    $html .= '<ul class="nav navbar-master navbar-nav navbar-right">';
    foreach ($menu as $r => $v){
        if ($v['menu'] == 'top-main' AND $v['menu'] != 'top-main-sub'){
            $html .= "<li class='navitem-".$v['class_name']."'><a href='../".$v['page']."'><i class='".$v['icon']."'></i> ".$v['name']."</a></li>";
        }
        if ($v['menu'] == 'top-main-sub' AND $v['parent_id'] == 0){
            $html .= "<li class='dropdown'>
            <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='".$v['icon']."'></i> ".$v['name']." <b class='caret'></b></a>
                <ul class='dropdown-menu'>";
            // Grab submenu items based on parent_id = $v['menu_id']
            $subs = gatherSubMenuItems($v['menu_id']);

            // If subs are found print them out to the parent element
            foreach ($subs as $s){
                $html .= "<li class='navitem-".$s['class_name']."'><a href='../".$s['page']."'><i class='".$s['icon']."'></i> ".$s['name']."</a></li>";
            }
            $html .= '</ul></li>';
        }
    }
    $html .= '
    </ul></div>
</nav>';

    $find = array_keys($hooks);
    $replace = array_values($hooks);

//Replace hooks
    $contents = str_replace($find, $replace, $html);

    return $contents;
}

// TODO: clear unspecified placeholders
function renderTemplate($template_file, $hooks = array()){    
    $path = LOCAL_ROOT . "/models/page-templates/" . $template_file;
    $contents = file_get_contents($path);
    
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

// UserCake's basic templating system.  Replaces hooks with specified text
function replaceDefaultHook($str)
{
	global $default_hooks,$default_replace;	
	return (str_replace($default_hooks,$default_replace,$str));
}

?>
