<? 
$fn = Zend_Registry::get('fn');
$action   = $fn->getReqParam('_spAction');
$clsName  = ucfirst($widget);
$funcName = "get" . ucfirst($action);
$clsInst  = getCPWidgetObj($widget);
$template = includeCPClass('Lib', 'template', 'Template');
$tpl = isset($_REQUEST['tpl'] ) ? $_REQUEST['tpl'] : 0;
$theme = Zend_Registry::get('currentTheme');

if ($action != ''){
    if ($cpCfg['cp.cssFramework'] == 'bootstrap' && $tpl == 1){
        print $theme->view->getMainThemeOutput();

    } else if (method_exists($clsInst, $funcName)){
        $showHTML = $fn->getReqParam('showHTML', 0);
        if ($showHTML == 0){
            print $clsInst->$funcName();
        } else {
            $content = $clsInst->$funcName();
            print "
            {$template->getHeader()}
            {$template->getBodySpAction($content, 'cp-widget')}
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
        print $error->getError('widgetMethodNotFound', $exp);    
    }

} else if (method_exists($clsInst, 'getWidget')){
    $showHTML = $fn->getReqParam('showHTML', 1);

    if ($showHTML == 0){
        print $clsInst->getWidget();
    } else {
        print "
        <!DOCTYPE html>
        <html>
            <head>
            </head>
            <body>
                {$clsInst->getWidget()}
            </body>
        </html>
        ";
    }
} else {
    $error = includeCPClass('Lib', 'Errors', 'Errors');
    $exp = array(
        'replaceArr' => array(
             'clsName' => $clsName
        )
    );
    print $error->getError('widgetNotFound', $exp);  
    
}
