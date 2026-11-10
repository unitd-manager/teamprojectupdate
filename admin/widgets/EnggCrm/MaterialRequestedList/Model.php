<?
class CPL_Admin_Widgets_EnggCrm_MaterialRequestedList_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
      	SELECT m.*
               
        FROM materials_request m
        LEFT JOIN project p ON (m.project_id = p.project_id)
      	
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'm';
        //to show only related quoteas matching to the product group for the staff with usergroup 'user'.
        

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_materialRequestedList');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}