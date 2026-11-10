<?
class CP_Admin_Modules_Event_Venue_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('event_venue');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 0
           ,'hasFlagInList' => 0
        ));
    }

    /**
     *
     */   
    
    //==================================================================//
    function setMediaArray($mediaArr) {
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('event_venue', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
            'count' => 'single'
        ));
    }

}