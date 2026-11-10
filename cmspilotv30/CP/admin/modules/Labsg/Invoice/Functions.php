<?
class CP_Admin_Modules_Labsg_Invoice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('labsg_invoice');
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