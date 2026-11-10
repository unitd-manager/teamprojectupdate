<?
class CP_Admin_Widgets_Tradingsg_QuoteByStaffChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

              //,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        $SQL = "
        SELECT q.*
              ,COUNT(q.quote_id) AS total_quote_monthly
              ,CONCAT_WS(' ', s.first_name ) AS staff_name
        FROM `quote` q
        LEFT JOIN staff s ON (s.staff_id = q.staff_id)
        ";

        /*$SQL = "
        SELECT s.*
              ,COUNT(q.staff_id) AS total_quote_monthly
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM `staff` s
        LEFT JOIN quote q ON (s.staff_id = q.staff_id)
        ";*/

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'q';

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $tomorrow = mktime(0,0,0,date("m"),date("d")+1,date("Y"));
        $today       = date('Y-m-d', $tomorrow);

        //$searchVar->sqlSearchVar[] = "(q.quote_date BETWEEN '{$last12Month}' AND '{$today}')";
        //$searchVar->groupBy = "DATE_FORMAT(q.quote_date, '%Y-%m')";
        $searchVar->sqlSearchVar[] ="s.status='Current'";
        $searchVar->groupBy = "q.staff_id";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_quoteByStaffChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}