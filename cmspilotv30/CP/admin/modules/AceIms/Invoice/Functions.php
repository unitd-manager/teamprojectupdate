<?
class CP_Admin_Modules_AceIms_Invoice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_invoice');
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
        $mediaObj = $mediaArr->getMediaObj('aceIms_invoice', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}