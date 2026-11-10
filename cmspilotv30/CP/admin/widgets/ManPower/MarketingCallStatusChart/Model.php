<?
class CP_Admin_Widgets_ManPower_MarketingCallStatusChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $current_year  = date('Y');
        $current_month = date('m');
        $startMonth    = $current_year . '-' . $current_month . '-' . '01';
        $endMonth      = $current_year . '-' . $current_month . '-' . '31';

        $SQL = "
        SELECT DISTINCT cr.status
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

        $current_year  = date('Y');
        $current_month = date('m');
        $startMonth    = $current_year . '-' . $current_month . '-' . '01';
        $endMonth      = $current_year . '-' . $current_month . '-' . '31';

        $searchVar->sqlSearchVar[] = "cr.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";

        $searchVar->sqlSearchVar[] = "cr.status != ''";
        $searchVar->sortOrder = 'cr.status ASC';
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