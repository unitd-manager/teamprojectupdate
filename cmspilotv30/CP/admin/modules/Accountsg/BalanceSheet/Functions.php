<?
class CP_Admin_Modules_Accountsg_BalanceSheet_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('accountsg_balanceSheet');
        $modObj['tableName'] = 'journal';
        $modObj['keyField']  = 'journal_id';
        //$modObj['listLimit'] = 2;
        $modObj['gotoLastPageByDefault'] = true;
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title' => 'Balance Sheet'
           ,'actBtnsList' => array('export')
           ,'actBtnsDetail' => array()
           ,'actBtnsNew' => array()
           ,'actBtnsEdit' => array()
        ));
    }
}