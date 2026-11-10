<?
class CP_Common_Modules_EnterpriseIms_Parent_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT DISTINCT p.parent_id
              ,p.email
              ,p.address_street
              ,p.address_area
              ,p.address_city
              ,p.address_state
              ,p.address_country
              ,p.address_po_code
              ,p.phone
              ,p.published
              ,p.creation_date
              ,p.modification_date
              ,p.pass_word
              ,p.subscribe
              ,p.first_name
              ,p.last_name
              ,p.mobile
              ,p.flag
              ,p.address_flat
              ,p.id_card_no
              ,p.emergency_contact_name
              ,p.emergency_contact_mobile
              ,p.created_by
              ,p.modified_by
              ,p.relationship_to_student
              ,p.emergency_relationship_to_student
              ,p.occupation
              ,p.company_id
              ,p.contact_id
              ,p.mode_of_payment
              ,p.cheque_no
              ,p.cheque_date
              ,p.bank_name
              ,p.giro_applicant_name
              ,p.dda
              ,p.account_name
              ,p.branch
              ,p.account_no
              ,p.with_drawal
              ,p.parent_code
              ,p.bank_code
              ,p.site_id
              ,gc2.name AS country_name
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
        LEFT JOIN (parent_contact pc)  ON (p.parent_id = pc.parent_id)
        LEFT JOIN (contact c) ON (pc.contact_id = c.contact_id)
        LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
        LEFT JOIN (company co) ON (p.company_id = co.company_id )
        LEFT JOIN geo_country gc2 ON (p.address_country = gc2.country_code)
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
