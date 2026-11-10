<?
class CP_Common_Modules_Directory_Guide_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT g.*
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
              ,(SELECT COUNT(*)
                FROM comment c
                WHERE c.record_id = g.guide_id
                  AND c.room_name = 'directory_guide'
              ) AS no_of_reviews
              ,(SELECT FORMAT(AVG(rating), 1)
                FROM comment cm
                WHERE cm.record_id = g.guide_id
                  AND room_name = 'directory_guide'
              ) AS avg_rating
              ,(SELECT COUNT(*)
                FROM guide_business gb
                WHERE gb.guide_id = g.guide_id
              ) AS no_of_business
        FROM guide g
        LEFT JOIN (contact c) ON (g.contact_id = c.contact_id)
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
        $searchVar->mainTableAlias = 'g';
        $contactId  = $fn->getIssetParam($this->expForSearchVar, 'contactId');
        $businessId  = $fn->getIssetParam($this->expForSearchVar, 'businessId');
        $specialFlag = $fn->getIssetParam($this->expForSearchVar, 'specialFlag');

        $guide_id = $fn->getReqParam('guide_id');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "g.published = 1";

            if($specialFlag == 'recommended'){
                $searchVar->sqlSearchVar[] = "g.recommended = 1";
            }
        }

        if ($businessId != ''){
            $searchVar->sqlSearchVar[] = "g.guide_id IN (
                SELECT guide_id
                FROM guide_business
                WHERE business_id = '{$businessId}'
            )";
        } else if ($guide_id != '') {
            $searchVar->sqlSearchVar[] = "g.guide_id = {$guide_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "g.guide_id = {$tv['record_id']}";
        } else {
            if ($contactId != '') {
                $searchVar->sqlSearchVar[] = "g.contact_id = {$contactId}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "( 
                    g.title LIKE '%{$tv['keyword']}%'  
                )";
            }
        }
        
		$searchVar->sortOrder = "g.guide_id DESC";
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

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'open_guide');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'record_type');
        $fa = $fn->addToFieldsArray($fa, 'recommended');
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);

        return $fa;
    }
}
