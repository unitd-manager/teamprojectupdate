<?
class CP_Admin_Modules_lawNews_correspondent_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('lawNews_correspondent');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
        ));
    }

    /**
     *
     */
     function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('lawNews_correspondent', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('lawNews_correspondent', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

     /**
     *
     */
    function setLinksArray($inst) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($cpCfg['cp.hasMultiSites']){
            $siteObj = getCPFnObj('common_site');
            $siteObj->setLinksArrayForSiteLink($inst, 'lawNews_correspondent');
        }
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('lawNews_correspondent', 'lawNews_reporterLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'reporter'
           ,'linkingType'  => 'portal'
        ));

        //------------------------------------------------------------------------------//
        $sqlYear = $fn->getValueListSQL('correspondentYear');
        $result = $db->sql_query($sqlYear);
        $yearArr = $dbUtil->getResultsetAsArrayForForm($result);

        $linkObj = $inst->getLinksArrayObj('lawNews_correspondent', 'lawNews_yearLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'    => 'correspondent_year'
            ,'linkingType'         => 'grid'
            ,'keyField'            => 'correspondent_year_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Year')
            ,'fieldClassArray'     => array()
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'dropdown', 'ddArr' => $yearArr)
            )
        ));

        //------------------------------------------------------------------------------//
        if($cpCfg['m.law.correspondent.showAdsBannerLink']){
            $bannerObj = getCPFnObj('ads_banner');
            $bannerObj->setLinksArrayForBannerLink($inst, 'lawNews_correspondent');
        }
    }


    /**
     *
     */
    function getYearsRow($displayTitle, $fieldName, $fieldValue = "", $extraParam = array()){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valArray = $fn->getValuelistValueAsArray('correspondentYear');

        $selectedValuesArr = explode(', ', $fieldValue);

        return $formObj->getCheckBoxArrRowByArr($displayTitle, $fieldName, $valArray, $selectedValuesArr);
    }
}