<?
class CP_Common_Modules_Museum_Facility_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('museum_facility');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('museum_facility', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('museum_facility', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}