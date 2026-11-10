<?
class CP_Admin_Widgets_ManPower_CallRegistryChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DISTINCT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,(SELECT COUNT(*)
                FROM call_registry cr1
                WHERE cr1.staff_id = s.staff_id
                ) AS no_of_calls
        FROM staff s
        JOIN (call_registry cr) ON (s.staff_id = cr.staff_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

        /*$last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $searchVar->sqlSearchVar[] = "(invoice_date BETWEEN '{$last12Month}' AND '{$today}')";
        $searchVar->groupBy = "DATE_FORMAT(invoice_date, '%Y-%m')";*/

        $searchVar->mainTableAlias = 's';
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_callRegistryChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}