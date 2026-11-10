<?
class CP_Admin_Modules_Project_Receipt_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_receipt');
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
        $mediaObj = $mediaArr->getMediaObj('project_receipt', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}