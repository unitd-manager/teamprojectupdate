<?
include("check.php");
$tv = Zend_Registry::get('tv');
$fn = Zend_Registry::get('fn');
$cpCfg = Zend_Registry::get('cpCfg');

$showHTML = $fn->getReqParam('showHTML', 1);
if($tv['action'] != "add" && $tv['action'] != "save" ) {

    //**********************************************************//
    $theme = Zend_Registry::get('currentTheme');
    
    if (!isLoggedInWWW()){
        $login = getCPPluginObj('member_login', false);
        $cpWWWUserNameC = $fn->getCookieParam('cpWWWUserNameC');
        $cpWWWPasswordC = $fn->getCookieParam('cpWWWPasswordC');
        $cpWWWLoginTypeC = $fn->getCookieParam('cpWWWLoginTypeC');
        $cpWWWRememberMeTokenC = $fn->getCookieParam('cpWWWRememberMeTokenC');

        if (($cpWWWUserNameC != "" && $cpWWWPasswordC != "" && $cpWWWLoginTypeC != "") ||
            ($cpCfg['cp.useRememberMeTokenForCookieLogin'] && $cpWWWRememberMeTokenC != '')){
            $login->model->loginWithCookie();
        }
    }

    if (!isLoggedInWWW() && $cpCfg['cp.siteLoginProtected']){
        $content = $theme->getLoginThemeOutput();
    } else if ($tv['spAction'] == "link"){
        $content = $theme->getLinkThemeOutput();
    } else {
        $content = $theme->getMainThemeOutput();
    }
    //**********************************************************//
    $template = includeCPClass('Lib', 'template', 'Template');
    $hiddenPageValues = '';
    if ($tv['spAction'] != "link"){
        $hiddenPageValues = $template->getHiddenPageValues();
    }

    if ($cpCfg['cp.cssFramework'] == 'bootstrap'){
        if ($showHTML == 1){
            $text = "{$content}";

            if ($cpCfg['cp.cleanHtml']){
                require_once("CleanHtml.php");
                $cleanObj = new CleanHtml();
                print $cleanObj->clean_html_code($text);
            } else {
                print $text;
            }
    
        } else {
            print "
            {$content}
            {$hiddenPageValues}
            ";
        }
    } else {
        if ($showHTML == 1){
            $text = "
            {$template->getHeader()}
            {$template->getBody($content, $hiddenPageValues)}
            {$template->getFooter()}
            ";
            
            if ($cpCfg['cp.cleanHtml']){
                require_once("CleanHtml.php");
                $cleanObj = new CleanHtml();
                print $cleanObj->clean_html_code($text);
            } else {
                print $text;
            }
    
        } else {
            print "
            {$content}
            {$hiddenPageValues}
            ";
        }
    }
}