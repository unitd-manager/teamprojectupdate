<? 
$fn = Zend_Registry::get('fn');
$action   = $fn->getReqParam('_spAction');
$clsName  = ucfirst($theme);
$funcName = "get" . ucfirst($action);
$clsInst  = getCPThemeObj($theme);

if ($action != ''){
    if (method_exists($clsInst, $funcName)){
        $showHTML = $fn->getReqParam('showHTML', 0);
        if ($showHTML == 0){
            print $clsInst->$funcName();
        } else {
            $template = includeCPClass('Lib', 'template', 'Template');
            $content = $clsInst->$funcName();
            print "
            {$template->getHeader()}
            {$template->getBodySpAction($content, 'cp-theme')}
            {$template->getFooter()}
            ";
        }
    } else {
        $error = includeCPClass('Lib', 'Errors', 'Errors');
        $exp = array(
            'replaceArr' => array(
                 'clsName' => $clsName
                ,'funcName' => $funcName
            )
        );
        print $error->getError('themeMethodNotFound', $exp);    
    }
}
