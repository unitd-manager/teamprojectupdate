<?
class CP_Common_Modules_Directory_Promotion3PartyLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('directory_promotion3PartyLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'promotion'
           ,'keyField'  => 'promotion_id'
           ,'mainModuleName' => 'directory_promotion'
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_promotion3PartyLink', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 90
            ,'maxHeightT' => 90
            ,'maxWidthN' => 192
            ,'maxHeightN' => 170
            ,'count' => 'single'
        ));
    }
}
