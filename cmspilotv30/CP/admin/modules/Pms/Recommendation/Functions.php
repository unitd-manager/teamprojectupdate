<?
class CP_Admin_Modules_Pms_Recommendation_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_recommendation');
        $modules->registerModule($modObj, array(
            'title'         => 'Recommendation'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pms_recommendation', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}