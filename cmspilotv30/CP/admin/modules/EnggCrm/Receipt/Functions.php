<?
class CP_Admin_Modules_EnggCrm_Receipt_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_receipt');
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

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enggCrm_receipt', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}