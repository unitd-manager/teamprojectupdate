<?
class CP_Common_Modules_Museum_Facility_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getFacilityList() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');

        $fnsModFacility = includeCPClass('ModuleFns', 'museum_facility');
        $SQL              = $fnsModFacility->getSQL();
        $result           = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $cls = ($row['title'] == $tv['facility']) ? " class='current'": "";
            $facility .= "
            <li>
                <a href=''{$cls}>{$row['title']}</a>
            </li>
            ";
        }

        $text = "
        <ul class='facility'>
            {$facility}
        </ul>
        ";

        return $text;
    }
}