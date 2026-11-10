<?
class CP_Admin_Modules_Common_LoginHistory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT lh.*
            ,DATE_FORMAT(lh.creation_date, '%d-%m-%Y') AS login_date
            ,DATE_FORMAT(lh.creation_date, '%H:%i')    AS login_time
            ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
            ,c.email AS contact_email
        FROM login_history lh
        LEFT JOIN (contact c) ON (c.contact_id = lh.contact_id )
        ";

        return $SQL;
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
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'active');
        return $fa;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $searchVar->mainTableAlias = 'lh';

        $creation_date1 = $fn->getReqParam('creation_date1');
        $creation_date2 = $fn->getReqParam('creation_date2');
        $contact_id     = $fn->getReqParam('contact_id');

        if ($tv['searchDone'] == 0){
            $searchVar->sqlSearchVar[] = "lh.active = 1";
        }

        //------------------------------------------------------------------------//
        if ($tv['special_search'] == "Active") {
            $searchVar->sqlSearchVar[] = "lh.active = 1";
        }

        if ($tv['special_search'] == "Not-Active") {
            $searchVar->sqlSearchVar[] = "(lh.active != 1 OR lh.active IS null)";
        }

        if ($creation_date1 != "" && $creation_date2 != "" ) {
            $searchVar->sqlSearchVar[] = "(lh.creation_date BETWEEN '{$creation_date1} 00:00:00' AND '{$creation_date2} 23:59:59')";
        } else if ($creation_date1 != "") {
            $searchVar->sqlSearchVar[] = "(lh.creation_date BETWEEN '{$creation_date1} 00:00:00' AND '{$creation_date1} 23:59:59')";
        }

        if ($contact_id != "") {
            $searchVar->sqlSearchVar[] = "lh.contact_id = '{$contact_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                c.first_name LIKE '%{$tv['keyword']}%' OR
                c.last_name LIKE '%{$tv['keyword']}%' OR
                c.email LIKE '%{$tv['keyword']}%' OR
                lh.ip_number LIKE '%{$tv['keyword']}%'
            )";
        }
    }
}
