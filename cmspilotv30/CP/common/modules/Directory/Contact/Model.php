<?
class CP_Common_Modules_Directory_Contact_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $interest_id = $fn->getReqParam('interest_id');
        $card_id = $fn->getReqParam('card_id');
        
        $extraTableNames = '';
        if ($interest_id != '') {
            $extraTableNames .= "JOIN interest_contact ic ON (c.contact_id = ic.contact_id)\n";
        }
        if ($card_id != '') {
            $extraTableNames .= "JOIN contact_card cc ON cc.contact_id = c.contact_id\n";
        }
        
        $extraSQL = '';
        if ($tv['catType'] == 'My Followers' || $tv['catType'] == 'Public Profile Followers'){
            $cpContactId = $fn->getSessionParam('cpContactId');
            $userType = $fn->getSessionParam('cpLoginTypeWWW');
            
            if ($userType == 'directory_contact'){
                $extraSQL = "
                ,(SELECT COUNT(*)
                  FROM contact_friend 
                  WHERE friend_id = c.contact_id
                  AND contact_id = '{$cpContactId}'
                 ) AS me_following_count
    
                ,(SELECT COUNT(*)
                  FROM contact_friend 
                  WHERE contact_id = c.contact_id
                  AND friend_id = '{$cpContactId}'
                 ) AS following_me_count
                ";
            }
        }

        $SQL   = "
        SELECT c.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
        	  ,ci.title AS city_name
        	  ,a.title AS area_name
        	  ,gc.name AS country_name
        
              ,(SELECT GROUP_CONCAT(cd.title ORDER BY cd.title SEPARATOR ', ')
                FROM cards cd, contact_card cc
                WHERE cc.contact_id = c.contact_id
                AND cd.card_id = cc.card_id
               ) AS cards_linked
        
              ,(SELECT COUNT(*)
                FROM comment 
                WHERE contact_id = c.contact_id
               ) AS comment_count
        
              ,(SELECT COUNT(*)
                FROM contact_friend 
                WHERE friend_id = c.contact_id
               ) AS no_of_followers

              ,(SELECT COUNT(*)
                FROM my_business mb
                WHERE mb.contact_id = c.contact_id
               ) AS my_business_count
               {$extraSQL}
        FROM contact c
        {$extraTableNames}
        LEFT JOIN (city ci) ON (c.city_id = ci.city_id)
        LEFT JOIN (area a) ON (c.area_id = a.area_id)
        LEFT JOIN (geo_country gc) ON (c.country_code = gc.country_code)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $relationalDataOnly  = $fn->getIssetParam($this->expForSearchVar, 'relationalDataOnly');
        $contactId  = $fn->getIssetParam($this->expForSearchVar, 'contactId');

        $interest_id    = $fn->getReqParam('interest_id');
        $contact_id     = $fn->getReqParam('contact_id');
        $card_id        = $fn->getReqParam('card_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');

        if ($relationalDataOnly){
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');
        } else if ($contact_id != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$tv['record_id']}'";
        } else if ($contactId != '' ) {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$contactId}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == 'Subscribed') {
                $searchVar->sqlSearchVar[] = 'c.subscribe = 1';
            }
    
            if ($tv['special_search'] == 'Not-Subscribed') {
                $searchVar->sqlSearchVar[] = '(c.subscribe != 1 OR c.subscribe IS null)';
            }
    
            if ($tv['special_search'] == 'Flagged') {
                $searchVar->sqlSearchVar[] = 'c.flag = 1';
            }
    
            if ($tv['special_search'] == 'Not-Flagged') {
                $searchVar->sqlSearchVar[] = '(c.flag != 1 OR c.flag IS null)';
            }
    
            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = 'c.published = 1';
            }
    
            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
            }
    
            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.first_name   LIKE '%{$tv['keyword']}%'
                    OR c.last_name    LIKE '%{$tv['keyword']}%'
                    OR c.email        LIKE '%{$tv['keyword']}%'
                )";
            }
        
            if ($interest_id != '' ) {
                $searchVar->sqlSearchVar[] = "ic.interest_id = {$interest_id}";
            }
        
            if ($card_id != '' ) {
                $searchVar->sqlSearchVar[] = "cc.card_id = {$card_id}";
            }
            
            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }
    
            $searchVar->sortOrder = "c.last_name, c.first_name";
        }
    }
    
    /**
     *
     */
    function getContactSQL() {
        $SQL = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name ) AS contact_name
        FROM contact
        WHERE published = 1
        ORDER BY contact_name
        ";

        return $SQL;
    }

    /**
     *
     */
    function getDirectoryContactDirectoryPreferenceLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');

        $catFld = ($formObj->mode == 'edit') ? 'c.category_id' : 'c.title AS category_title';
        $subCatFld = ($formObj->mode == 'edit') ? 'sc.sub_category_id'  : 'sc.title AS sub_category_title';

        $SQL = "
        SELECT cs.contact_preference_id
              ,{$catFld}
              ,{$subCatFld}
        FROM contact_preference cs 
        LEFT JOIN category c ON (c.category_id = cs.category_id)
        LEFT JOIN sub_category sc ON (sc.sub_category_id = cs.sub_category_id)
        WHERE cs.contact_id = '{$id}'
        ORDER BY cs.contact_preference_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function getDirectoryContactDirectoryCardsLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');
        $cardFld = ($formObj->mode == 'edit') ? 'c.card_id' : 'c.title AS card_title';

        $SQL = "
        SELECT cc.contact_card_id
              ,{$cardFld}
        FROM contact_card cc 
        LEFT JOIN cards c ON (c.card_id = cc.card_id)
        WHERE cc.contact_id = '{$id}'
        ORDER BY cc.contact_card_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function getDirectoryContactDirectoryAreaLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');

        $countryFld = ($formObj->mode == 'edit') ? 'c.country_id' : 'c.title AS country_title';
        $areaFld = ($formObj->mode == 'edit') ? 'a.area_id'  : 'a.title AS area_title';

        $SQL = "
        SELECT ca.contact_area_id
              ,ca.title
              ,{$countryFld}
              ,{$areaFld}
        FROM contact_area ca
        LEFT JOIN country c ON (c.country_id = ca.country_id)
        LEFT JOIN area a ON (ca.area_id = a.area_id)
        WHERE ca.contact_id = '{$id}'
        ORDER BY ca.contact_area_id
        ";

        return $SQL;
    }
}
