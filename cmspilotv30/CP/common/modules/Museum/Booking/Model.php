<?
class CP_Common_Modules_Museum_booking_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT b.* 
              ,f.title AS facility_title
        FROM booking b
        LEFT JOIN (facility f) ON (b.facility_id = f.facility_id)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'b';

        $facility_id = $fn->getReqParam('facility_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "b.booking_id = {$tv['record_id']}";
        } else {
            if ($facility_id != '') {
                $searchVar->sqlSearchVar[] = "b.facility_id = {$facility_id}";
            }
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "( 
                f.title   LIKE '%{$tv['keyword']}%'
                OR b.organisation   LIKE '%{$tv['keyword']}%'
                OR b.first_name   LIKE '%{$tv['keyword']}%'
                OR b.last_name   LIKE '%{$tv['keyword']}%'
                OR b.email   LIKE '%{$tv['keyword']}%'
                OR b.phone   LIKE '%{$tv['keyword']}%'
                OR b.comments   LIKE '%{$tv['keyword']}%'
                )";
        }

        $searchVar->sortOrder = "b.booking_id DESC";
    }

}
