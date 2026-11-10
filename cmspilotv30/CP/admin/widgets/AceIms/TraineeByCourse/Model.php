<?
class CP_Admin_Widgets_AceIms_TraineeByCourse_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT c.title AS course_title
			  ,(SELECT COUNT(*)
				FROM course_contact cc
				WHERE cc.batch_id = b.batch_id
                AND b.status = 'Open'
				) AS attendee_count
        FROM batch b
        JOIN course c ON (c.course_id = b.course_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $searchVar->mainTableAlias = 'b';

        $searchVar->sqlSearchVar[] = "b.status = 'Open'";

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "b.creation_date >= '{$start_date}'";
        }
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "b.creation_date <= '{$end_date}'";
        }

        $searchVar->groupBy = 'c.course_id';
        $searchVar->sortOrder = 'course_title';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_traineeByCourse');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    function getExportToExcel($dataArray = ''){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
         
        $fa = array(
              'course_title'    => $phpExcel->getFldObj('Course')
             ,'attendee_count'  => $phpExcel->getFldObj('Total')
        );

        $file_name = "TraineeByCourse_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}