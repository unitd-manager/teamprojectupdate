<?
class CP_Common_Modules_AgileIms_Student_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {


        $SQL = "
        SELECT s.*
              ,gc.name AS country_name
              ,gc2.name AS c_country_name
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS contact_name
              ,IF(s.company_id > 0, c.title, s.company_name) AS company_title
              ,c.title                AS c_company_name
              ,c.email                AS c_email
              ,c.address1             AS c_address_flat
              ,c.address2             AS c_address_street
              ,c.address_town         AS c_address_town
              ,c.address_state        AS c_address_state
              ,c.address_po_code      AS c_address_po_code
              ,c.phone                AS c_phone
              ,c.fax                  AS c_fax
        FROM student s
        LEFT JOIN (company c) ON (s.company_id = c.company_id )
        LEFT JOIN geo_country gc ON (s.address_country_code = gc.country_code)
        LEFT JOIN geo_country gc2 ON (c.address_country_code = gc.country_code)
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
        $searchVar->mainTableAlias = 's';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.student_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.student_id');
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               s.first_name LIKE '%{$tv['keyword']}%'
            OR s.last_name LIKE '%{$tv['keyword']}%'
            )";
        }
    }
}
