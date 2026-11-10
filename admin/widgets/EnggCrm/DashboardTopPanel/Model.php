<?
class CPL_Admin_Widgets_EnggCrm_DashboardTopPanel_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT o.*
        FROM opportunity_project_history o
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $searchVar->sqlSearchVar[] = "o.date != '' ";
        $searchVar->sqlSearchVar[] = "(o.alert_status IS NULL OR o.alert_status = '' OR o.alert_status = 0)";
        $searchVar->sortOrder = "o.opportunity_project_history_id DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_notifications');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     */
    function getUpdateIsRead() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $opportunity_project_history_id     = $fn->getReqParam('opportunity_project_history_id');

        $sqlUpdate = "
        UPDATE `opportunity_project_history` SET alert_status = 1
        WHERE opportunity_project_history_id = {$opportunity_project_history_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }
}