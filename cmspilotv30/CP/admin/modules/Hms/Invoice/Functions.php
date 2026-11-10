<?
class CP_Admin_Modules_Hms_Invoice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_invoice');
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