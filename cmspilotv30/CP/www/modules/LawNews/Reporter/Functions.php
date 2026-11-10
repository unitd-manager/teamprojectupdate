<?
class CP_Www_Modules_LawNews_Reporter_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('lawNews_reporter');
        $modules->registerModule($modObj, array(
             'tableName' => 'reporter'
            ,'keyField'  => 'reporter_id'
        ));
    }
    
    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('lawNews_reporter', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('lawNews_reporter', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }   
}
