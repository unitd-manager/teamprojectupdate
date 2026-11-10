<?php
//==================================================================//
function checkLoggedIn() {
    $cpUtil = Zend_Registry::get('cpUtil');
    $cpUrl = Zend_Registry::get('cpUrl');

    if (@$_SESSION['cpIsLoggedInWWW'] == false || @$_SESSION['cpIsLoggedInWWW'] == '') {
        $loginUrl = $cpUrl->getUrlBySecType('Login');
        $cpUtil->redirect($loginUrl);
    }
}

/**
 *
 * @return <type>
 */
function isLoggedInWWW(){
    return @$_SESSION['cpIsLoggedInWWW'];
}
