<?
class CP_Admin_Modules_Pos_Package_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_package');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'printListScreen')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pos_package', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_package', 'pos_productLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'package_product'
            ,'showAnchorInLinkPortal' => 1
        ));
    }
}