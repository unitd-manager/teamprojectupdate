<?
$showHTML   = isset( $_REQUEST['showHTML'] ) ? $_REQUEST['showHTML'] : "1";
$hasDB      = isset( $_REQUEST['hasDB'] )    ? $_REQUEST['hasDB']    : "0";
$actionName = ucfirst($tv['spAction']);
$textTemp   = "";
$arrayMasterLink = Zend_Registry::get('arrayMasterLink');

//------------------------------------------------------------------------//
if ($tv['lnkRoom'] != ''
    && $tv['spAction'] != 'createDeleteLinkRecord'
    && $tv['spAction'] != 'createDeleteLinkAllRecords'
    && $tv['spAction'] != 'linkPortalRecordsByFilter'
    ) {

    $modObj = Zend_Registry::get('currentModule');

    $actionTemp = "get{$actionName}";

    if ($tv['srcRoom'] != '') {
        $funcName = "setLinksArray";
        $srcObj = getCPModuleObj($tv['srcRoom']);
        if (method_exists($srcObj->fns, $funcName)) {
            $srcObj->fns->$funcName($arrayMasterLink);
        }
    }

    if ($actionName == 'DeletePortalRecordByID') {
        if (method_exists($modObj, $actionTemp)){
            $modObj->$actionTemp();
        } else {
            $spActionObj = includeCPClass('Lib', 'SpecialAction');
            $textTemp .= $spActionObj->getDeletePortalRecordByID();
        }

    } else { //for new / edit portal
        if (method_exists($modObj, $actionTemp)){
            $textTemp .= $modObj->$actionTemp();
        } else {
            $clsName = ucfirst($tv['module']);
            print "<h3>{$clsName}->{$actionTemp} does not exist s</h3>";
            exit();
        }
    }

} else if ($tv['module'] != ""){
    $modObj = Zend_Registry::get('currentModule');

    $actionTemp = "get{$actionName}";  //eg: getSelectMedia

    if (method_exists($modObj, $actionTemp)){
        $textTemp .= $modObj->$actionTemp();
    } else {
        $clsName = ucfirst($tv['module']);
        $error = includeCPClass('Lib', 'Errors', 'Errors');
        $exp = array(
            'replaceArr' => array(
                 'clsName' => $clsName
                ,'funcName' => $actionTemp
            )
        );
        print $error->getError('moduleMethodNotFound', $exp);
        exit();
    }

//------------------------------------------------------------------------//
} else {
    $clsInst = includeCPClass('Lib', 'SpecialAction');
    $actionTemp = "get{$actionName}";

    if (method_exists($clsInst, $actionTemp)){
        $textTemp .= $clsInst->$actionTemp();
    } else {
        //$cpUtil->redirect("index.php");
    }
}

//------------------------------------------------------------------------//
$template = includeCPClass('Lib', 'template', 'Template');

if ($showHTML == 1){
    print "
    {$template->getHeader()}
    {$template->getBodySpAction($textTemp)}
    {$template->getFooter()}
    ";
} else {
   print $textTemp;
}
