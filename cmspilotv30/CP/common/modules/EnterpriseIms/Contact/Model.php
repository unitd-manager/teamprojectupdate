<?
class CP_Common_Modules_EnterpriseIms_Contact_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $course_id      = $fn->getReqParam('course_id');
        $interest_id    = $fn->getReqParam('interest_id');
        $course_status  = $fn->getReqParam('course_status');

        $extraFieldNames = '';
        $extraTableNames = '';

        if ($interest_id != "") {
            $extraTableNames .= "JOIN interest_contact ic ON (c.contact_id = ic.contact_id)";
        }

        if ($course_id != "" || ($course_status != '' && $cpCfg['m.enterpriseIms.course.hasCourseContactStatus'])) {
            if ($course_status != '') {
                $extraFieldNames .= ",cc.course_status";
            } else {
                $extraFieldNames .= ",cc.batch_id";
            }
            
            $extraTableNames .= "JOIN course_contact cc ON (c.contact_id = cc.contact_id)";
        }
        
        $SQL = "
        SELECT c.*
              ,gc.name AS country_name
              ,gc2.name AS c_country_name
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
              ,IF(c.company_id > 0, co.title, c.company_name) AS company_title
              ,co.title                AS c_company_name
              ,co.email                AS c_email
              ,co.address1             AS c_address_flat
              ,co.address2             AS c_address_street
              ,co.address_town         AS c_address_town
              ,co.address_state        AS c_address_state
              ,co.address_po_code      AS c_address_po_code
              ,co.phone                AS c_phone
              ,co.fax                  AS c_fax
              ,co.category             AS c_category
              ,co.reg_number           AS c_reg_number
              {$extraFieldNames}
        FROM contact c
        LEFT JOIN (company co) ON (c.company_id = co.company_id )
        LEFT JOIN geo_country gc ON (c.address_country = gc.country_code)
        LEFT JOIN geo_country gc2 ON (co.address_country_code = gc2.country_code)
        {$extraTableNames}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getSQLForPager() {
        $fn = Zend_Registry::get('fn');

        $class_id        = $fn->getReqParam('class_id');
        $interest_id     = $fn->getReqParam('interest_id');
        $course_id       = $fn->getReqParam('course_id');
        $course_status   = $fn->getReqParam('course_status');
        $extraTableNames = '';

        if ($class_id != "") {
            $extraTableNames .= "JOIN student_class sc ON (c.contact_id = sc.contact_id)";
        }

        if ($interest_id != "") {
            $extraTableNames .= "JOIN interest_contact ic ON (c.contact_id = ic.contact_id)";
        }

        $SQL = "
        SELECT count(c.contact_id)
        FROM contact c
        LEFT JOIN (company co) ON (c.company_id = co.company_id )
        LEFT JOIN geo_country gc ON (c.address_country = gc.country_code)
        LEFT JOIN geo_country gc2 ON (co.address_country_code = gc2.country_code)
        JOIN course_contact cc ON (c.contact_id = cc.contact_id)
        {$extraTableNames}
        ";

        return $SQL;
    }


    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $searchVar->mainTableAlias = 'c';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   c.first_name LIKE '%{$tv['keyword']}%'
                OR c.last_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }

    }
}
