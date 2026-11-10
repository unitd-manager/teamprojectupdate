<?
class CP_Www_Modules_Museum_Collection_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('museum_collection');
        $modules->registerModule($modObj, array(
             'tableName' => 'collection'
            ,'keyField'  => 'collection_id'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('museum_collection', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
