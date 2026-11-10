<?
class CP_Common_Modules_Pms_Parent_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT p.*
              ,gc2.name AS c_country_name
              ,CONCAT_WS(' ', p.first_name, p.last_name ) AS parent_name
              ,co.title                AS c_company_name
              ,co.email                AS c_email
              ,co.address1             AS c_address_flat
              ,co.address2             AS c_address_street
              ,co.address_town         AS c_address_town
              ,co.address_state        AS c_address_state
              ,co.address_po_code      AS c_address_po_code
              ,co.phone                AS c_phone
              ,co.fax                  AS c_fax
        FROM parent p
        LEFT JOIN (company co) ON (p.company_id = co.company_id )
        LEFT JOIN geo_country gc2 ON (co.address_country_code = gc2.country_code)
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
        
        $searchVar->mainTableAlias = 'p';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.parent_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.parent_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   p.first_name LIKE '%{$tv['keyword']}%'
                OR p.last_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }

    }
}
