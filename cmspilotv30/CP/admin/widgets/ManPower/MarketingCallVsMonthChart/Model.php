<?
class CP_Admin_Widgets_ManPower_MarketingCallVsMonthChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $year  = date('Y');
        $startYear = $year .'-01-01'; 
        $endYear   = $year .'-12-31';

        $SQL = "
        SELECT DISTINCT MONTHNAME(cr.contact_date) AS month
        FROM call_registry cr
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

        $searchVar->mainTableAlias = 'cr';

        $year  = date('Y');
        $startYear = $year .'-01-01'; 
        $endYear   = $year .'-12-31';
        
        $searchVar->sqlSearchVar[] = "cr.contact_date BETWEEN '{$startYear}' AND '{$endYear}'";

        //$searchVar->sqlSearchVar[] = "cr.status != ''";
        $searchVar->sortOrder = '{MONTHNAME(cr.contact_date)}';
    }

    /**
     *
     */
    function getDataArray() {

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_marketingCallVsMonthChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}