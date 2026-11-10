<? 
$fn = Zend_Registry::get('fn');
$action   = $fn->getReqParam('_spAction');
$clsName  = ucfirst($plugin);
$funcName = "get" . ucfirst($action);
$clsInst  = getCPPluginObj($plugin);

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
    print $error->getError('pluginMethodNotFound', $exp);

}
