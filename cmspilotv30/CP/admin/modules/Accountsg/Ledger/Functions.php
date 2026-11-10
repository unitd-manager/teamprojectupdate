<?
class CP_Admin_Modules_Accountsg_Ledger_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('accountsg_ledger');
        $modObj['tableName'] = 'journal';
        $modObj['keyField']  = 'journal_id';
        //$modObj['listLimit'] = 2;
        //$modObj['gotoLastPageByDefault'] = true;
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'actBtnsList' => array('export')
           ,'actBtnsDetail' => array()
           ,'actBtnsNew' => array()
           ,'actBtnsEdit' => array()
        ));
    }
}