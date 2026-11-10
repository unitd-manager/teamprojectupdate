<?
class CP_Admin_Widgets_ManPower_OpportunityByMonthChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DATE_FORMAT(o.enquiry_date, '%M') AS opportunity_month
              ,DATE_FORMAT(o.enquiry_date, '%Y') AS opportunity_year
              ,(SUM(o.estimated_value)) AS total_estimated_value
        FROM opportunity o
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

        $searchVar->mainTableAlias = 'o';

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $searchVar->sqlSearchVar[] = "(enquiry_date BETWEEN '{$last12Month}' AND '{$today}')";
        $searchVar->groupBy = "DATE_FORMAT(enquiry_date, '%Y-%m')";
    }

    /**
     *
     */
    function getDataArray() {

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_opportunityByMonthChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}