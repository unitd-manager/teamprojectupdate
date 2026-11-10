<?
class CP_Admin_Modules_Edukloud_Recommendation_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukloud_recommendation');
        $modules->registerModule($modObj, array(
            'title'         => 'Recommendation'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukloud_recommendation', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}