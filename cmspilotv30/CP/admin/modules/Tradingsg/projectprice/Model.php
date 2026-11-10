<?
class CP_Admin_Modules_Tradingsg_tasks_Model extends CP_Common_Lib_ModuleModelAbstract
{
   function getSQL() {
$SQL = "
        SELECT t.* 
	
       
				  FROM tasks t
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

        $tasks_id = $fn->getReqParam('tasks_id');
       $status_filter = $fn->getReqParam('status_filter');

        if ($tasks_id != "") {
            $searchVar->sqlSearchVar[] = "t.tasks_id = '{$tasks_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.tasks_id = '{$tv['record_id']}'";
        }  else {
        if ($status_filter != '') {
                $searchVar->sqlSearchVar[] = "t.status_filter = '{$status_filter}'";
            }
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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        
        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
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

     //function getEditPortalValidate() {
       // return $this->getNewValidate();
   // }

    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

         if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
       
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

       
        $fa = $fn->addToFieldsArray($fa, 'tasks_id');
        $fa = $fn->addToFieldsArray($fa, 'task1');
       
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'status_filter');
        $fa = $fn->addToFieldsArray($fa, 'description');
      
       

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

	

}
