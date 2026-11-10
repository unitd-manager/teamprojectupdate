<?
class CP_Admin_Modules_Ecommerce_Country_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_country');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'hasMultiLang' => 1            
        ));
    }
    
    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');

        $country_id   = $fn->getReqParam('country_id');
        $country_name = $fn->getReqParam('country_name');

        if ($country_id != '') {
            $searchVar->sqlSearchVar[] = "c.country_id = {$country_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.country_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.country_id');
            
            if ($country_name != ''){
                $searchVar->sqlSearchVar[] = "c.country_name = '{$country_name}'";
            }

            if ($tv['keyword'] != ''){
                $searchVar->sqlSearchVar[] = "( c.country_name LIKE '%{$tv['keyword']}%'
                                             OR c.value        LIKE '%{$tv['keyword']}%'
                                             )";
            }
        }
        $searchVar->sortOrder = 'c.country_id';
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $linkObj = $inst->getLinksArrayObj('common_country', 'common_languageLink', array(
             'historyTableName'       => 'language'
            ,'showAnchorInLinkPortal' => 0
            ,'hasPortalEdit'          => 1
            ,'hasPortalDelete'        => 1
            ,'linkingType'            => 'portal'
            ,'fieldlabel'             => array(
                 'Language'
                ,'Lang Prefix'
                ,'Published'
           	)
        ));
        $inst->registerLinksArray($linkObj);
    }

    /**
     *
     */
    function getCountryLanguageLink($mainRoomName, $linkRoomName, $id) {
    
        $SQL = "
        SELECT a.language_id
              ,a.title
              ,a.lang_prefix
        FROM country b
            ,language a
       	WHERE a.country_id = b.country_id
       	AND b.country_id = {$id}
        ";
               
         return $SQL;
    
    }   
}