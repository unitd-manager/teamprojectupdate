<?
class CP_Admin_Widgets_ManPower_OpportunityPositionChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $current_year  = date('Y');
        $current_month = date('m');
        $startMonth    = $current_year . '-' . $current_month . '-' . '01';
        $endMonth      = $current_year . '-' . $current_month . '-' . '31';

        $appendSql = '';
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $appendSql = "AND op.site_id = {$_SESSION['cp_site_id']}";
        }

        $SQL = "
        SELECT DISTINCT o.position
              ,(
                SELECT COUNT(*) FROM opportunity op
                WHERE op.position = o.position
                  AND op.creation_date BETWEEN '{$startMonth}' AND '{$endMonth}'
                  {$appendSql}
              ) AS total_count_oppurtunity
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

        $current_year  = date('Y');
        $current_month = date('m');
        $startMonth    = $current_year . '-' . $current_month . '-' . '01';
        $endMonth      = $current_year . '-' . $current_month . '-' . '31';

        $searchVar->sqlSearchVar[] = "o.creation_date BETWEEN '{$startMonth}' AND '{$endMonth}'";

        $searchVar->sqlSearchVar[] = "o.position != ''";
        $searchVar->sortOrder = 'o.position ASC';
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