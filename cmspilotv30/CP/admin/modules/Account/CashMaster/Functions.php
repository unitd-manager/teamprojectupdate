<?
class CP_Admin_Modules_Account_CashMaster_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('account_cashMaster');
        $modObj['tableName'] = 'journal_master';
        $modObj['keyField']  = 'journal_master_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title' => 'Cash'
           ,'actBtnsList' => array('cashReceipt', 'cashPayment')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsNew' => array()
           ,'actBtnsEdit' => array('delete')
           ,'scrollContent' => false
        ));
    }

    function afterDeleteHandler($journal_master_id){
        $db = Zend_Registry::get('db');

        $SQL = "
        DELETE FROM journal
        WHERE journal_master_id = {$journal_master_id}
        ";
        $result = $db->sql_query($SQL);

    }
}
