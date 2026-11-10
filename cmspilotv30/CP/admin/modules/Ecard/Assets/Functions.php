<?
class CP_Admin_Modules_Ecard_Assets_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecard_assets');

        $modules->registerModule($modObj, array(
           'hasMultiLang'  => 1
        ));
    }

    //==================================================================//
    function getContentRecordTypeArray(){
        $arr=
        array(
             "Ecard"
            ,"Music"
        );

        return $arr;
    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ecard_assets', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthN'  => '318'
            ,'maxHeightN' => '250'
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ecard_assets', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ecard_assets', 'music', 'music');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    //==================================================================//

}