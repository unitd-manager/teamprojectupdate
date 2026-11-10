<?
class CP_Common_Modules_WebBasic_Team_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('webBasic_team');
        $modules->registerModule($modObj, array(
        ));
    }
    
    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_team', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }    
}