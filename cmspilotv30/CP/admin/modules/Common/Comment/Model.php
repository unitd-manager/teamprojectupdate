<?
class CP_Admin_Modules_Common_Comment_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT c.*
              ,IF (c.room_name = 'directory_guide', g.title, b.business_name) AS record_title
              ,IF (c.room_name = 'directory_guide', 'Guide', 'Business') AS recType
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
        FROM `comment` c
        LEFT JOIN (business b) ON (c.record_id = b.business_id)
        LEFT JOIN (guide g) ON (c.record_id = g.guide_id)
        JOIN (contact cont) ON (c.contact_id = cont.contact_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';
        $special_search = $fn->getReqParam('special_search');
        $business_id = $fn->getReqParam('business_id');
        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "c.comment_id  = '{$tv['record_id']}'";

        } else {
            //$searchVar->sqlSearchVar[] = "c.room_name = 'directory_business'";
            if ($business_id != '' ) {
                $searchVar->sqlSearchVar[] = "c.record_id = '{$business_id}'";
            }

            if ($special_search != '' ) {
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "c.published = 1";
                }
    
                if ($special_search == 'Not-Published' ) {
                    $searchVar->sqlSearchVar[] = "(c.published = 0 OR c.published IS NULL OR c.published = '')";
                }

                if ($special_search == 'Flagged') {
                    $searchVar->sqlSearchVar[] = "c.flag = 1";
                }
    
                if ($special_search == 'Flagged' ) {
                    $searchVar->sqlSearchVar[] = "(c.flag = 0 OR c.flag IS NULL OR c.flag = '')";
                }

                if ($special_search == 'Abusive') {
                    $searchVar->sqlSearchVar[] = "c.abusive = 1";
                }

                if ($special_search == 'Not-Abusive' ) {
                    $searchVar->sqlSearchVar[] = "(c.abusive = 0 OR c.abusive IS NULL OR c.abusive = '')";
                }

                if ($special_search == 'Verified') {
                    $searchVar->sqlSearchVar[] = "c.verified = 1";
                }

                if ($special_search == 'Not-Verified' ) {
                    $searchVar->sqlSearchVar[] = "(c.verified = 0 OR c.verified IS NULL OR c.verified = '')";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.subject LIKE '%{$tv['keyword']}%'
                    OR c.name LIKE '%{$tv['keyword']}%'
                    OR c.email LIKE '%{$tv['keyword']}%'
                    OR c.comments LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "c.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

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

        $fa = $fn->addToFieldsArray($fa, 'comments');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'abusive');
        $fa = $fn->addToFieldsArray($fa, 'verified');
        return $fa;
    }
}
