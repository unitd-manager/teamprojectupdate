<?
class CP_Admin_Modules_Hms_Receipt_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('hms_receipt');
        $modules->registerModule($modObj, array(
            'tableName'   => 'receipt'
           ,'keyField'    => 'receipt_id'
           ,'actBtnsList' => array()
           ,'actBtnsDetail' => array()
           ,'hasEditInList' => false
        ));
    }
}