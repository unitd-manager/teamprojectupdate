<? 
$fn = Zend_Registry::get('fn');
$action   = $fn->getReqParam('_spAction');
$clsName  = ucfirst($widget);
$funcName = "get" . ucfirst($action);
$clsInst  = getCPWidgetObj($widget);

if ($action != ''){
    if (method_exists($clsInst, $funcName)){
        print $clsInst->$funcName();
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
        <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
        <html xmlns=\"http://www.w3.org/1999/xhtml\">
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
