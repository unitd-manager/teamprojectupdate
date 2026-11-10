<?
class CP_Admin_Modules_Edukloud_Refund_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukloud_refund');
        $modules->registerModule($modObj, array(
            'title'         => 'Refund'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukloud_refund', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}