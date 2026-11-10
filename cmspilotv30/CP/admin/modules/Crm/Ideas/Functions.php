<?
class CP_Admin_Modules_Crm_Ideas_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('crm_ideas');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 0
           ,'hasFlagInList'=> 0
           ,'title'        => 'Ideas / Development'
        ));
    }

    /**
     *
     * @return <type>
     */    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('crm_ideas', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}