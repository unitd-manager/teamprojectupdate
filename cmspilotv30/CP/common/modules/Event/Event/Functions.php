<?
class CP_Common_Modules_Event_Event_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('event_event');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     * @return <type>
     */
     function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('event_event', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('event_event', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('event_event', 'sponsorsPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('event_event', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}