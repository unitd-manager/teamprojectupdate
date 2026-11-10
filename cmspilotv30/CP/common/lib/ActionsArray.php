<?
class CP_Common_Lib_ActionsArray
{
    var $actionsArr = array();

    //==================================================================//
    function __construct(){
        $this->setActionsArray();

        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $arr = $cpCfg['cp.availableModGroups'];

        foreach($arr as $modGroup){
            $fnModGrp = includeCPClass('ModGroup', $modGroup, 'Functions');
            if (method_exists($fnModGrp, 'setActionsArray')) {
                $fnModGrp->setActionsArray($this);
            }
        }

        if ($tv['module'] != ''){
            $modObj = getCPFnObj($tv['module']);
            if (method_exists($modObj, 'setActionsArray')) {
                $modObj->setActionsArray($this);
            }
        }

        //FB::log($this->actionsArr);
    }

    //==================================================================//
    function registerAction($actObj, $overrideArr){
        foreach($overrideArr as $key => $value){
            $actObj[$key] = $value;
        }

        $this->actionsArr[$actObj['name']] = $actObj;
    }

    //==================================================================//
    function setActionsArray(){
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $pager = Zend_Registry::get('pager');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $searchQueryString = $pager->searchQueryString;
        $searchQueryString = preg_replace('/&_action=[a-zA-Z0-9\. _,]+&?/', "&", $searchQueryString);
        if (substr($searchQueryString, -1) == "&") {
           $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
        }

        $searchQueryStringWithQnMark = $searchQueryString . $cpUrl->getQnMarkForUrl($searchQueryString);

        $queryStringNoRoomInfo = $_SERVER['QUERY_STRING'];
        $queryStringNoRoomInfo = $_SERVER['QUERY_STRING'];
        $queryStringNoRoomInfo = preg_replace('/_topRm=[a-zA-Z0-9\. _,]+&?/', '&', $queryStringNoRoomInfo);
        $queryStringNoRoomInfo = preg_replace('/&module=[a-zA-Z0-9\. _,]+&?/', '&', $queryStringNoRoomInfo);
        $queryStringNoRoomInfo = preg_replace('/&_action=[a-zA-Z0-9\. _,]+&?/', '&', $queryStringNoRoomInfo);

        //====================== Publish ================================//
        $actObj = $this->getActionObj('publish');
        $this->registerAction($actObj, array(
            'url' =>  "javascript:Actions.saveList('publish')"
        ));

        //====================== Unpublish ================================//
        $actObj = $this->getActionObj('unPublish');
        $this->registerAction($actObj, array(
            'url' =>  "javascript:Actions.saveList('unPublish')"
        ));

        //====================== Flag ================================//
        $actObj = $this->getActionObj('flag');
        $this->registerAction($actObj, array(
            'url' =>  "javascript:Actions.saveList('flag')"
        ));

        //====================== Unflag ================================//
        $actObj = $this->getActionObj('unFlag');
        $this->registerAction($actObj, array(
            'url' =>  "javascript:Actions.saveList('unFlag')"
        ));

        //====================== New ================================//
        $actObj = $this->getActionObj('new');

        $newUrl = "{$searchQueryStringWithQnMark}&_action=new&lang=eng";
        if ($cpCfg['cp.useSEOUrl'] == 1){
            $queryStringOnly = ($pager->queryStringOnly != '') ? '?' . $pager->queryStringOnly : '';
            $newUrl = $pager->urlStringOnly . 'new/' . $queryStringOnly;
        }

        $this->registerAction($actObj, array(
            'url' =>  $newUrl
           ,'title' => "{$ln->gd('cp.actionButton.lbl.new', 'New')}"
        ));

        //====================== Edit ================================//
        $actObj = $this->getActionObj('edit');

        $editUrl = "{$searchQueryStringWithQnMark}&_action=edit";
        if ($cpCfg['cp.useSEOUrl'] == 1 && strpos($searchQueryString, '/detail/') !== false){
            $editUrl = str_replace('/detail/', '/edit/', $searchQueryString);
        }

        $this->registerAction($actObj, array(
            'url' => $editUrl
           ,'title' => "{$ln->gd('cp.actionButton.lbl.edit', 'Edit')}"
        ));

        //====================== Delete ================================//
        $actObj = $this->getActionObj('delete');
        $this->registerAction($actObj, array(
            'url' => "javascript:Actions.deleteRecord('{$tv['module']}')"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.delete', 'Delete')}"
        ));

        //====================== Duplicate ================================//
        $actObj = $this->getActionObj('duplicate');
        $this->registerAction($actObj, array(
            'url' => "javascript:Actions.duplicateRecord('{$tv['topRm']}', '{$tv['module']}')"
        ));

        //====================== Delete List ================================//
        $actObj = $this->getActionObj('deleteList');
        $this->registerAction($actObj, array(
            'url'   => "javascript:Actions.saveList('delete')"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.delete', 'Delete')}"
        ));

        //====================== Save & Continue ================================//
        $actObj = $this->getActionObj('addNew');
        $this->registerAction($actObj, array(
             'url' => "#"
            ,'title' => "Save & Continue"
        ));

        //======================  ================================//
        $actObj = $this->getActionObj('cancelNew');
        $this->registerAction($actObj, array(
             'url' => "#"
            ,'title' => "Cancel"
       ));

        //====================== Save ================================//
        $actObj = $this->getActionObj('save');
        $this->registerAction($actObj, array(
            'url' => "#"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.save', 'Save')}"
        ));

        //====================== Apply ================================//
        $actObj = $this->getActionObj('apply');
        $this->registerAction($actObj, array(
            'url' => "javascript:Actions.apply('{$tv['module']}')"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.apply', 'Apply')}"
        ));

        //====================== Cancel ================================//
        $actObj = $this->getActionObj('cancel');
        $this->registerAction($actObj, array(
            'url' => "#"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.cancel', 'Cancel')}"
        ));

        //====================== Export ================================//
        $actObj = $this->getActionObj('export');
        $this->registerAction($actObj, array(
            'url' => "{$searchQueryString}&_spAction=exportData&showHTML=0&export=1&hasDB=1"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.export', 'Export')}"
        ));

        //====================== Import ================================//
        $actObj = $this->getActionObj('import');
        $this->registerAction($actObj, array(
            'url' => "javascript:Actions.importData('{$tv['module']}', 'default')"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.import', 'Import')}"
        ));

        //====================== Update ================================//
        $actObj = $this->getActionObj('update');
        $this->registerAction($actObj, array(
            'url' => "javascript:Actions.updateData('{$tv['module']}', 'default')"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.update', 'Update')}"
        ));

        //====================== Print ================================//
        $actObj = $this->getActionObj('print');
        $this->registerAction($actObj, array(
            'url' => "{$searchQueryString}&_spAction=printData&showHTML=0&hasDB=1"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.print', 'Print')}"
        ));

        //====================== Search ================================//
        $actObj = $this->getActionObj('search');
        $this->registerAction($actObj, array(
            'url' => "{$searchQueryString}&_action=search"
           ,'title' => "{$ln->gd('cp.actionButton.lbl.search', 'Search')}"
        ));

        //====================== Perform Search ================================//
        $actObj = $this->getActionObj('performSearch');
        $this->registerAction($actObj, array(
            'title' => 'Submit'
           ,'url' => "javascript:document.forms['search'].submit();"
        ));

        //====================== Print List Screen ================================//
        $actObj = $this->getActionObj('printListScreen');
        $this->registerAction($actObj, array(
            'title' => 'Print'
           ,'url' => "javascript:Actions.printListScreen('{$searchQueryString}&_action=print')"
        ));

        //====================== Print List to PDF ================================//
        $actObj = $this->getActionObj('printListPDF');
        $this->registerAction($actObj, array(
            'title' => 'Print'
           ,'url' => "javascript:Actions.printListPDF('{$queryStringNoRoomInfo}')"
        ));

        //====================== Print List To PDF PHPExcel ================================//
        $actObj = $this->getActionObj('printListPDFPHPExcel');
        $this->registerAction($actObj, array(
             'title' => 'Print'
            ,'url' => "{$searchQueryString}&_spAction=printListToPDFPHPExcel&showHTML=0&hasDB=1"
        ));

        //====================== Print Detail to PDF ================================//
        $actObj = $this->getActionObj('printDetailPDF');
        $this->registerAction($actObj, array(
            'title' => 'Print'
           ,'url' => "javascript:Actions.printDetailPDF()"
        ));

        //====================== Print Detail Menu ================================//
        $actObj = $this->getActionObj('printDetailMenu');
        $this->registerAction($actObj, array(
            'title' => 'Print'
           ,'url' => "javascript:Actions.printDetailMenu('{$searchQueryString}')"
        ));

        //====================== Reports ================================//
        $actObj = $this->getActionObj('reportsMenu');
        $this->registerAction($actObj, array(
            'title' => 'Reports'
           ,'url' => "javascript:Actions.openReportsMenu('{$searchQueryString}');"
        ));

        //====================== Reports ================================//
        $actObj = $this->getActionObj('changePassword');
        $this->registerAction($actObj, array(
             'title' => $ln->gd('w.member.changePassword.heading')
            ,'url' => "{$cpCfg['cp.scopeRootAlias']}index.php?module=membership_contact&_spAction=changePassword&showHTML=0"
       ));

        //====================== Print Po to PDF Used in All Trading sites ================================//
        $actObj = $this->getActionObj('printPOPDFList');
        $this->registerAction($actObj, array(
            'title' => 'Print PO'
           ,'url' => "javascript:Actions.printPOPDFList('{$queryStringNoRoomInfo}')"
        ));

        //====================== Print Invoice to PDF Used in Engex site ================================//
        $actObj = $this->getActionObj('printInvoicePDFList');
        $this->registerAction($actObj, array(
            'title' => 'Print Invoice'
           ,'url' => "javascript:Actions.printInvoicePDFList('{$queryStringNoRoomInfo}')"
        ));
    }

    //==================================================================//
    function getActionObj($action){

        $arr['name']   = $action;
        $arr['title']  = ucfirst($action);
        $arr['url']    = 'javascript:void();';

        return $arr;
    }

}