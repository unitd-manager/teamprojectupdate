<?
class CP_Common_Modules_Directory_Menu_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT DISTINCT m.*
        	  ,b.business_name
        	  ,mc.title AS menu_category_title
        	  ,msc.title AS menu_sub_category_title
        FROM menu m
        JOIN (business b) ON (m.business_id = b.business_id)
        LEFT JOIN (menu_category mc) ON (mc.menu_category_id = m.menu_category_id)
        LEFT JOIN (menu_sub_category msc) ON (msc.menu_sub_category_id = m.menu_sub_category_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'm';
        $businessId = $fn->getIssetParam($this->expForSearchVar, 'businessId');
        $menuCatId = $fn->getIssetParam($this->expForSearchVar, 'menuCatId');
        $menu_id   = $fn->getReqParam('menu_id');
        $business_id = $fn->getReqParam('business_id');
        $menu_category_id = $fn->getReqParam('menu_category_id');
        $menu_sub_category_id = $fn->getReqParam('menu_sub_category_id');
        $special_search  = $fn->getReqParam('special_search');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "m.published = 1";

            if ($tv['secType'] == 'Business Menu'){
                $cpBusinessId = $fn->getSessionParam('cpBusinessId');
                $searchVar->sqlSearchVar[] = "m.business_id = '{$cpBusinessId}'";
            }
        }

        if ($menuCatId != '') {
            $searchVar->sqlSearchVar[] = "m.menu_category_id  = {$menuCatId}";
    	}

        if ($businessId != '') {
    	    $searchVar->sqlSearchVar[] = "m.business_id = '{$businessId}'";
        } else if ($menu_id != '') {
            $searchVar->sqlSearchVar[] = "m.menu_id = {$menu_id}";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "m.menu_id = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'm.menu_id');

            if ($business_id != '' ) {
                $searchVar->sqlSearchVar[] = "m.business_id = '{$business_id}'";
            }

            if ($menu_category_id != '') {
                $searchVar->sqlSearchVar[] = "m.menu_category_id  = {$menu_category_id}";
            }

            if ($menu_sub_category_id != '') {
                $searchVar->sqlSearchVar[] = "m.menu_sub_category_id = '{$menu_sub_category_id}'";
            }

            if ($special_search != '' ) {
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "m.published = 1";
                }

                if ($special_search == 'Not-Published') {
                    $searchVar->sqlSearchVar[] = "m.published = 0 OR m.published IS NULL OR m.published = ''";
                }

                if ($special_search == 'Latest' ) {
                    $searchVar->sqlSearchVar[] = "m.latest = 1";
                }

                if ($special_search == 'Flag' ) {
                    $searchVar->sqlSearchVar[] = "m.flag = 1";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    m.title LIKE '%{$tv['keyword']}%'
                    OR m.description  LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
    
    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('business_name', 'Please choose the business');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        if ($tv['lang'] == 'eng') {
            $validate->validateData('title', 'Please enter the title');
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields() {
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'business_id');
        $fa = $fn->addToFieldsArray($fa, 'menu_category_id');
        $fa = $fn->addToFieldsArray($fa, 'menu_sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'product_code');
        $fa = $fn->addToFieldsArray($fa, 'latest');
        return $fa;
    }
}
