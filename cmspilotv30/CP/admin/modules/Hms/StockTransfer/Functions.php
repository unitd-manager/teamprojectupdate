<?
class CP_Admin_Modules_Hms_StockTransfer_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_stockTransfer');
        $modules->registerModule($modObj, array(
            'tableName' => 'stock_transfer'
            ,'hasFlagInList' => 0
           ,'keyField'         => 'stock_transfer_id'
           ,'actBtnsList'   => array('new')
           ,'actBtnsEdit'   => array('save','apply')
           ,'title'     => 'Stock Transfer'
        ));
    }
}