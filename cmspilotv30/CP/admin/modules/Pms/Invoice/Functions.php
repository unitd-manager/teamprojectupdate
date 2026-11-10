<?
class CP_Admin_Modules_Pms_Invoice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_invoice');
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
        $mediaObj = $mediaArr->getMediaObj('pms_invoice', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}