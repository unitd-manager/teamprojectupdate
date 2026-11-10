<?
class CP_Admin_Modules_Tradingsg_Invoice_Functions
{
    /*
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_invoice');
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
        ));
    }
    */
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_invoice');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
          ,'actBtnsDetail' => array()
          ,'hasEditInList' => false
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

    }}