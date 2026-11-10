<? 
$fn = Zend_Registry::get('fn');
$theme = Zend_Registry::get('currentTheme');
$cpCfg = Zend_Registry::get('cpCfg');
$tpl = isset($_REQUEST['tpl'] ) ? $_REQUEST['tpl'] : 0;
/******************************************************************************/
$action   = $fn->getReqParam('_spAction');
$clsName  = ucfirst($plugin);
$funcName = "get" . ucfirst($action);
$clsInst  = getCPPluginObj($plugin);
if ($cpCfg['cp.cssFramework'] == 'bootstrap' && $tpl == 1){
    print $theme->view->getMainThemeOutput();
} else {
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
}
