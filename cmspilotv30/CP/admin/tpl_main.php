<?
include("check.php");
$tv = Zend_Registry::get('tv');
$fn = Zend_Registry::get('fn');
$cpUrl = Zend_Registry::get('cpUrl');

$showHTML = $fn->getReqParam('showHTML', 1);

if($tv['action'] != "add" && $tv['action'] != "save" ) {

    //**********************************************************//
    $theme = getCPThemeObj($cpCfg['cp.theme']);
    if (!isLoggedInAdmin()){
        $login = getCPPluginObj('common_login');
        $login->loginWithCookie();
        $content = $theme->getLoginThemeOutput();
    } else if (changePasswordOnLogin()){
        $content = $theme->getChangePasswordOnLoginThemeOutput();
    } else if ($tv['spAction'] == "link"){
        $content = $theme->getLinkThemeOutput();
    } else {
        $content = $theme->getMainThemeOutput();
    }

    $template = includeCPClass('Lib', 'template', 'Template');
    if ($showHTML == 1){

        $text = "
        {$template->getHeader()}
        {$template->getBody($content)}
        {$template->getFooter()}
        ";

        require_once("CleanHtml.php");
        $cleanObj = new CleanHtml();
        //print $cleanObj->clean_html_code($text);
        print $text;

    } else {
        $hiddenPageValues = ''; // not sure whether this needed - chk and remove
        print "
        {$content}
        {$hiddenPageValues}
        ";
    }
}
