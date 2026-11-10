<?
class CP_Common_Modules_Directory_Promotion_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT DISTINCT p.*
        	  ,b.business_name
        	  ,c.title AS card_title
        	  ,b.category_id
        	  ,b.sub_category_id
        	  ,cat.title AS category_title
        	  ,sc.title AS sub_category_title
        	  ,CONCAT_WS(' ', p.end_date, p.end_time) promoDateTime
        FROM promotion p
        JOIN (business b) ON (p.business_id = b.business_id)
        LEFT JOIN address ad ON ad.address_id = b.address_id
        LEFT JOIN (cards c) ON (p.card_id = c.card_id)
        LEFT JOIN (category cat) ON (cat.category_id = b.category_id)
        LEFT JOIN (sub_category sc) ON (sc.sub_category_id = b.sub_category_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $promoId     = $fn->getIssetParam($this->expForSearchVar, 'promoId');
        $relDataOnly = $fn->getIssetParam($this->expForSearchVar, 'relationalDataOnly');
        $busCategoryId = $fn->getIssetParam($this->expForSearchVar, 'busCategoryId');
        $specialFlag = $fn->getIssetParam($this->expForSearchVar, 'specialFlag');
        $businessId = $fn->getIssetParam($this->expForSearchVar, 'businessId');

        $business_id = $fn->getReqParam('business_id');
        $record_type = $fn->getReqParam('record_type');
        $card_id     = $fn->getReqParam('card_id');
        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "b.published = 1";
            $country_id = $fn->getSessionParam('cp_country_id');

            if ($country_id != ''){
        	    $searchVar->sqlSearchVar[] = "ad.country_id = '{$country_id}'";
            }
        }

        if ($relDataOnly){
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.promotion_id');
        } else if ($businessId != '') {
    	    $searchVar->sqlSearchVar[] = "p.business_id = '{$businessId}'";
        } else if ($tv['record_id'] != '' && $specialFlag != 'promoSlideshow') {
            $searchVar->sqlSearchVar[] = "p.promotion_id = '{$tv['record_id']}'";
        } else if ($promoId != '') {
            $searchVar->sqlSearchVar[] = "p.promotion_id = '{$promoId}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.promotion_id');

        	if ($business_id != '' ) {
        	    $searchVar->sqlSearchVar[] = "p.business_id = '{$business_id}'";
        	}

        	if ($busCategoryId != '' ) {
        	    $searchVar->sqlSearchVar[] = "b.category_id = '{$busCategoryId}'";
        	}

        	if ($record_type != '') {
        	    $searchVar->sqlSearchVar[] = "p.record_type = '{$record_type}'";
        	}

        	if ($card_id != '' ) {
        	    $searchVar->sqlSearchVar[] = "p.card_id = '{$card_id}'";
        	}

        	if ($start_date != "" && $end_date != "" ) {
        	    $searchVar->sqlSearchVar[] = "(p.start_date AND p.end_date BETWEEN '{$start_date} 00:00:00' AND '{$end_date} 23:59:59')";
        	}

        	if ($tv['keyword'] != "") {
        	    $searchVar->sqlSearchVar[] = "(
        	        p.title   LIKE '%{$tv['keyword']}%' OR 
        	        p.custom_text   LIKE '%{$tv['keyword']}%'
                )";
        	}

            if($specialFlag == 'promoSlideshow'){
			    $searchVar->sortOrder = 'promoDateTime';
            } else {
			    $searchVar->sortOrder = "p.promotion_id DESC";
            }
		}
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

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
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

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
        $fn = Zend_Registry::get('fn');
        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'business_id');
        $fa = $fn->addToFieldsArray($fa, 'record_type');
        $fa = $fn->addToFieldsArray($fa, 'promo_type');
        $fa = $fn->addToFieldsArray($fa, 'title', @$fa['promo_type']);
        $fa = $fn->addToFieldsArray($fa, 'heading');
        $fa = $fn->addToFieldsArray($fa, 'custom_text');
        $fa = $fn->addToFieldsArray($fa, 'card_id');
        $fa = $fn->addToFieldsArray($fa, 'start_date');
        $fa = $fn->addToFieldsArray($fa, 'end_date');
        $fa = $fn->addToFieldsArray($fa, 'start_time');
        $fa = $fn->addToFieldsArray($fa, 'end_time');
        $fa = $fn->addToFieldsArray($fa, 'days_of_week');

        $fa = $fn->addToFieldsArray($fa, 'promo_type_custom');
        $fa = $fn->addToFieldsArray($fa, 'special_price');
        $fa = $fn->addToFieldsArray($fa, 'discount_percentage');
        $fa = $fn->addToFieldsArray($fa, 'item_type');
        $fa = $fn->addToFieldsArray($fa, 'rrp');
        $fa = $fn->addToFieldsArray($fa, 'limited_avail');
        $fa = $fn->addToFieldsArray($fa, 'limited_availability');
        $fa = $fn->addToFieldsArray($fa, 'status');

        $fa = $fn->addToFieldsArray($fa, 'poster_type');
        $fa = $fn->addToFieldsArray($fa, 'offer_by');
        $fa = $fn->addToFieldsArray($fa, 'generic_poster_name');
        $fa = $fn->addToFieldsArray($fa, 'item_name');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'repeat');
        $fa = $fn->addToFieldsArray($fa, 'days_of_month');
        $fa = $fn->addToFieldsArray($fa, 'promotion_url');

        return $fa;
    }
}