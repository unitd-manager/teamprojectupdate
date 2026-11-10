<?
class CP_Admin_Modules_Edukloud_Invoice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_invoice');
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
        $mediaObj = $mediaArr->getMediaObj('edukloud_invoice', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}