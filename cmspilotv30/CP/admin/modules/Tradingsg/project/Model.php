<?
class CP_Admin_Modules_Tradingsg_project_Model extends CP_Common_Lib_ModuleModelAbstract
{
   function getSQL() {
$SQL = "
        SELECT p.* 
	
       
				  FROM project p
        ";

        return $SQL;
    }

    /**
     *
     */
   
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $project_id = $fn->getReqParam('project_id');
       

        if ($project_id != "") {
            $searchVar->sqlSearchVar[] = "p.project_id = '{$project_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.project_id = '{$tv['record_id']}'";
        } 
    }

    /**
     *
     */
     function getNewValidate() {
       $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');
      
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
		
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
		 $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

       // $validate->validateData('project_id', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
      
    }

    
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

       
        $fa = $fn->addToFieldsArray($fa, 'project_id');
        $fa = $fn->addToFieldsArray($fa, 'title');
       
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'start_date');
        $fa = $fn->addToFieldsArray($fa, 'end_date');
       

        return $fa;
    }

   
   /* function getprojectJsonByComId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $project_id = $fn->getReqParam('project_id', '', true);

        $json  = array();

        if ($project_id == ''){
            $json[] = array('value' => '', 'caption' => 'Please Select');
            return json_encode($json);
        }

        $SQL = $this->getContactsByCompanySQL($project_id);
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['project_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }*/
/*function getTradingsgprojectTradingsgtasksSQL($id) {

        return "
        SELECT t.*
        FROM tasks t
       
        ";
    }
	*/
           
       function gettasksValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $tasks_id  = $fn->getReqParam('tasks_id');

        $validate->resetErrorArray();

        $validate->validateData('task1', 'Please enter the task1');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     */
    function getAddtasksSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->gettasksValidate()){
            return $validate->getErrorMessageXML();
        }

        $tasks_id  = $fn->getReqParam('tasks_id');
        $task1      = $fn->getPostParam('task1');
       // $agent_commission = $fn->getPostParam('agent_commission');
        $date   = $fn->getPostParam('date');
        $description   = $fn->getPostParam('description');

        $fa = array();
        $fa['tasks_id']       = $tasks_id;
        $fa['date']            = $date;
        $fa['task1']   = $task1;
        $fa['description']              = $description;
       // $fa['date']    = date("Y-m-d H:i:s");
       // $fa['created_by']       = $fn->getSessionParam('userName');

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'project');
        $result = $db->sql_query($insert);

        $fa1 = array();
        $fa1['date']            = $date;
        $fa1['task1']              = $task1;
        $fa1['description']   = $description;

        $whereCondition = "WHERE description = {$description}";
        $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa1, "tasks", $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

   
}
