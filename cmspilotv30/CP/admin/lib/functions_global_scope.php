<?php
//==================================================================//
function checkLoggedIn() {
    $cpUtil = Zend_Registry::get('cpUtil');
    $tv = Zend_Registry::get('tv');

    if (@$_SESSION['isLoggedInAdmin'] == false || @$_SESSION['isLoggedInAdmin'] == '') {
        if ($tv["topRm"] != "security" && $tv['module'] != "media" && $tv['spAction'] == '') {
            $_SESSION['pageToRedirectAfterLogin'] = $_SERVER['REQUEST_URI'];
        } else {
            $_SESSION['pageToRedirectAfterLogin'] = '';
        }
        $cpUtil->redirect("index.php");
    }
}

/**
 *
 * @return <type>
 */
function isLoggedInAdmin(){
    return @$_SESSION['isLoggedInAdmin'];
}

/**
 * 
 * @return type
 */
function changePasswordOnLogin(){
    $cpCfg = Zend_Registry::get('cpCfg');
    if($cpCfg['m.core.staff.hasChangePasswordNextLogin']){
        return @$_SESSION['changePasswordOnLogin'];
    }
    return 0;
}

function redirectToFirstRoom() {
    $cpUtil = Zend_Registry::get('cpUtil');
    $tv = Zend_Registry::get('tv');

    if ($_SERVER['QUERY_STRING'] == '' || $_SERVER['QUERY_STRING'] == 'logged_in=1') {
        $url = "index.php?_topRm={$tv['topRm']}&module={$tv['module']}";
        $cpUtil->redirect($url);
    }
}
