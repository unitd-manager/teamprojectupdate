<?
class CP_Admin_Modules_Logistics_Vehicle_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('logistics_vehicle');
        $modules->registerModule($modObj, array(
             'actBtnsList' => array('new','import')
        ));
    }

    /**
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('logistics_vehicle', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }

}