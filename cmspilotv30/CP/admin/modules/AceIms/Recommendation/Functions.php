<?
class CP_Admin_Modules_AceIms_Recommendation_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_recommendation');
        $modules->registerModule($modObj, array(
            'title'         => 'Recommendation'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('aceIms_recommendation', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}