<?
class CP_Admin_Widgets_ManPower_MarketingCallHistoryTodayChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $today  = date('Y-m-d');

        $SQL = "
        SELECT DISTINCT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,(SELECT COUNT(*) 
                FROM call_registry cry
                WHERE cry.contact_date = '{$today}'
                  AND cry.site_id = {$_SESSION['cp_site_id']}
                  AND cry.staff_id = s.staff_id
              ) AS total_count_status
        FROM call_registry cr
        JOIN (staff s) ON (s.staff_id = cr.staff_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

        $searchVar->mainTableAlias = 'cr';

        $today  = date('Y-m-d');

        $searchVar->sqlSearchVar[] = "cr.contact_date = '{$today}'";

        $searchVar->sqlSearchVar[] = "cr.contact_date != ''";
        $searchVar->sortOrder = 's.staff_id ASC';
    }

    /**
     *
     */
    function getDataArray() {

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_marketingCallHistoryTodayChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}