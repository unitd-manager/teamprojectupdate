<?
class CP_Admin_Modules_Event_Fixture_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('event_fixture');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 0
           ,'hasFlagInList' => 0
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $category_id    = $fn->getReqParam('category_id');
        $fixture_id     = $fn->getReqParam('fixture_id');
        $status         = $fn->getReqParam('status');

        if ($fixture_id != "") {
            $searchVar->sqlSearchVar[] = "f.fixture_id = '{$fixture_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "f.fixture_id = '{$tv['record_id']}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'f.fixture_id');
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }
    
            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(c.subscribe != 1 OR c.subscribe IS null)";
            }
    
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }
    
            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "c.published = 1";
            }
    
            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
            }
    
            //------------------------------------------------------------------------//
            
            if ($category_id!= '' ) {
                $searchVar->sqlSearchVar[] = "f.category_id = {$category_id}";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "f.status = '{$status}'";
            }
        }
    }

    /**
     *
     * @return <type>
     */    
     function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('event_fixture', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('event_fixture', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    //==================================================================//
    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('event_fixture', 'event_fixtureContactLink');

        $sqlContact = $this->getSQLContact();
        $result = $db->sql_query($sqlContact);
        $contactArr = $dbUtil->getResultsetAsArrayForForm($result);

        $statusArr = array('In', 'May be', 'Out');            

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'    => 'fixture_contact'
            ,'linkingType'         => 'grid'
            ,'showLinkPanelInNew'  => 0
            ,'keyField'            => 'fixture_contact_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Name', 'Status', 'Position', 'Tries', 'Points', 'Comments')
            ,'fieldClassArray'     => array('', '', '', '', '', 'w150')
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                 array('type' => 'dropdown', 'ddArr' => $contactArr)
                ,array('type' => 'dropdown', 'ddArr' => $statusArr, 'useKey' => 0)
            )
            ,'hideFieldsArray' => array('contact_name')
        ));
    }
}