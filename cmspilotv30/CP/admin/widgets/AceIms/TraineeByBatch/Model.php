<?
class CP_Admin_Widgets_AceIms_TraineeByBatch_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');

        $specialSearch = $fn->getReqParam('specialSearch');
        
        $sqlAppend = '';
        if ($specialSearch) {
            $sqlAppend .= "AND b.status = '{$specialSearch}'";
        }

        $SQL = "
        SELECT c.title AS course_title
              ,b.status
              ,b.title AS batch_title
              ,b.venue 
              ,b.start_time 
              ,b.end_time 
              ,t.first_name as trainer_name
			  ,(SELECT COUNT(*)
				FROM course_contact cc
				WHERE cc.batch_id = b.batch_id
                {$sqlAppend}
				) AS attendee_count
        FROM batch b
        LEFT JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN teacher t ON (t.teacher_id = b.teacher_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'b';
        
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $specialSearch  = $fn->getReqParam('specialSearch');
        $c = &$this->controller;

        if($c->searcVarCondn){
            $searchVar->sqlSearchVar[] = "{$c->searcVarCondn}";
        }

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "b.start_date >= '{$start_date}'";
        }

        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "b.end_date <= '{$end_date}'";
        }

        if ($specialSearch != ''){
            $searchVar->sqlSearchVar[] = "b.status = '{$specialSearch}'";
        }

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_traineeByBatch');

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
             ,'batch_title'     => $phpExcel->getFldObj('Batch')
             ,'venue'           => $phpExcel->getFldObj('Venue')
             ,'start_time'      => $phpExcel->getFldObj('Start Time')
             ,'end_time'        => $phpExcel->getFldObj('End Time')
             ,'trainer_name'    => $phpExcel->getFldObj('Trainer Name')
             ,'attendee_count'  => $phpExcel->getFldObj('Number of trainee')
        );

        $file_name = "TraineeByBatch_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}