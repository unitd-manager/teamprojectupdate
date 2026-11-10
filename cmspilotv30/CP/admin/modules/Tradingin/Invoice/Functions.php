<?
class CP_Admin_Modules_Tradingin_Invoice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingin_invoice');
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