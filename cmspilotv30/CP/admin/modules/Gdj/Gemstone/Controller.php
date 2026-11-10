<?
class CP_Admin_Modules_Gdj_Gemstone_Controller extends CP_Common_Modules_Gdj_Gemstone_Controller
{
    //==================================================================//
    function getDeleteRecordInBulk() {
    	exit();
        set_time_limit(50000);
        $spActionObj = includeCPClass('Lib', 'SpecialAction');
        $db = Zend_Registry::get('db');
        $SQL = "
        SELECT product_id
        FROM product
        WHERE record_type = 'Gemstone'
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $spActionObj->getDeleteRecordByID('gdj_gemstone', $row['product_id']);
        }
    }
}