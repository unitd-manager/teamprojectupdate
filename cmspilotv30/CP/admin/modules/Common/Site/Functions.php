<?
class CP_Admin_Modules_Common_site_Functions extends CP_Common_Modules_Common_site_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
    	
        $modObj = $modules->getModuleObj('common_site');
        $modules->registerModule($modObj, array(
            'hasMultiLang'  => 1
           ,'title' => 'Sites'
        ));
    }


    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        //$colorArr = $fn->getValuelistValueAsArray('productColor');
        $langArr = $cpCfg['cp.availableLanguages'];

        $linkObj = $inst->getLinksArrayObj('common_site', 'common_languageLink', array(
             'historyTableName'    => 'language'
            ,'historyTableKeyField'=> 'language_id'
            ,'linkingType'         => 'grid'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Language  Display', 'Language', 'Published')
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                   array('type' => 'textbox')
                  ,array('type' => 'dropdown', 'ddArr' => $langArr, 'useKey' => 1)
                  ,array('type' => 'singleCheckbox')
            )
            ,'mainRoomKeyFldNameInHistTbl' => 'site_id'
        ));
        $inst->registerLinksArray($linkObj);
    }
    
    /**
     *
     */
    function setLinksArrayForSiteLink($linksArrObj, $module){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
    	
        $siteArr = $fn->getDdDataAsArray('common_site');
        $linkObj = $linksArrObj->getLinksArrayObj($module, 'common_siteLink');

        $linksArrObj->registerLinksArray($linkObj, array(
             'historyTableName'    => 'site_link'
            ,'historyTableKeyField'=> 'site_link_id'
            ,'linkingType'         => 'grid'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Site', 'Published')
            ,'fieldClassArray'     => array('', 'w100 txtCenter')
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                   array('type' => 'dropdown', 'ddArr' => $siteArr)
                  ,array('type' => 'singleCheckbox')
            )
            ,'moduleForHistory' => $module
            ,'mainRoomKeyFldNameInHistTbl' => 'record_id'
            ,'additionalFieldsArray'=> array('b.published')
        ));

    }
}