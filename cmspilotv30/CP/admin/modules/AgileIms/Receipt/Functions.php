<?
class CP_Admin_Modules_AgileIms_Receipt_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_receipt');
        $modules->registerModule($modObj, array(
           'title'         => 'Receipt'
          ,'actBtnsList'   => array()
          ,'actBtnsDetail' => array()
          ,'hasEditInList' => false
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('agileIms_receipt', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}