<?
class CP_Www_Modules_LawNews_Correspondent_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('lawNews_correspondent');
        $modules->registerModule($modObj, array(
             'tableName' => 'correspondent'
            ,'keyField'  => 'correspondent_id'
        ));
    }
    
    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('lawNews_correspondent', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }    
}
