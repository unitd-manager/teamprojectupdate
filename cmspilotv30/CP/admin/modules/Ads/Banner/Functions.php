<?
class CP_Admin_Modules_Ads_Banner_Functions extends CP_Common_Modules_Ads_Banner_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ads_banner');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
        ));

    }
    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        if($cpCfg['cp.hasMultiSites']){
            $siteObj = getCPFnObj('common_site');
            $siteObj->setLinksArrayForSiteLink($inst, 'ads_banner');
        }

    }    
    /**
     *
     */
    function setLinksArrayForBannerLink($linksArrObj, $module){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');        

        $bannerArr = $fn->getDdDataAsArray('ads_banner');
        $positionArr = $cpCfg['m.ads.bannerLink.positionArr'];
        $linkObj = $linksArrObj->getLinksArrayObj($module, 'ads_bannerLink');

        $linksArrObj->registerLinksArray($linkObj, array(
             'historyTableName'    => 'banner_link'
            ,'historyTableKeyField'=> 'banner_link_id'
            ,'linkingType'         => 'grid'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Banner', 'Position', 'Sort Order', 'Published')
            ,'fieldClassArray'     => array('', '', 'w50 txtCenter', 'w100 txtCenter')
            ,'additionalFieldsArray'=> array('b.banner_position', 'b.sort_order','b.published')

            ,'gridFieldTypeArray'  => array(
                   array('type' => 'dropdown', 'ddArr' => $bannerArr)
                  ,array('type' => 'dropdown', 'ddArr' => $positionArr)                   
                  ,array('type' => 'textbox')
                  ,array('type' => 'singleCheckbox')
            )
            ,'showAnchorInLinkPortal' => false
            ,'moduleForHistory' => $module
            ,'mainRoomKeyFldNameInHistTbl' => 'record_id'

        ));
    }

}