<?
class CP_Admin_Modules_Ecard_Contact_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = $modules->getModuleObj('ecard_contact');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media')
           ,'title'         => 'Contact'
           ,'actBtnsList'   => array('new', 'import', 'export')
        ));
    }

    //==================================================================//
    //==================================================================//
    /**
     *
     * @return <type>
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('contact', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $interest_id    = $fn->getReqParam('interest_id');
        $contact_id     = $fn->getReqParam('contact_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');

        if ($contact_id != "") {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$tv['record_id']}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');
    
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
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.first_name   LIKE '%{$tv['keyword']}%'
                    OR c.last_name    LIKE '%{$tv['keyword']}%'
                    OR c.company_name LIKE '%{$tv['keyword']}%'
                    OR c.email        LIKE '%{$tv['keyword']}%'
                )";
            }
        
            if ($interest_id != '' ) {
                $searchVar->sqlSearchVar[] = "ic.interest_id = {$interest_id}";
            }
            
            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }
    
            $searchVar->sortOrder = "c.last_name, c.first_name";
        }
    }

    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ecard_contact', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'interest_contact'
           ,'showAnchorInLinkPortal' => 0
        ));
    }
    //==================================================================//

}