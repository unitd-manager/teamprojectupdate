<?
class CP_Admin_Modules_Logistics_Invoice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('logistics_invoice');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array('export')
          ,'actBtnsDetail' => array()
          ,'hasEditInList' => false
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('logistics_invoice', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}