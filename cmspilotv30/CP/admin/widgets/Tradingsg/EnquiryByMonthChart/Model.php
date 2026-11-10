<?
class CP_Admin_Widgets_Tradingsg_EnquiryByMonthChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DATE_FORMAT(e.creation_date, '%M') AS enquiry_month
              ,COUNT(e.enquiry_id) AS total_enquiry_monthly
        FROM enquiry e
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'e';

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $tomorrow = mktime(0,0,0,date("m"),date("d")+1,date("Y"));
        $today       = date('Y-m-d', $tomorrow);

        $searchVar->sqlSearchVar[] = "(e.creation_date BETWEEN '{$last12Month}' AND '{$today}')";
        $searchVar->groupBy = "DATE_FORMAT(e.creation_date, '%Y-%m')";

        if ($_SESSION['userGroupType'] == "User") {
            $searchVar->sqlSearchVar[] = "e.staff_id =  {$_SESSION['staff_id']}";
        }

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_enquiryByMonthChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}