<?
class CP_Admin_Modules_Subscription_Invoice_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('subscription_invoice');
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
        $mediaObj = $mediaArr->getMediaObj('subscription_invoice', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));                
    }
}