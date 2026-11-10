<?
class CP_Admin_Modules_Tradingsg_project_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
     function getprojectJsonByComId() {
        return $this->model->getContactJsonByComId();
    }
	
	 function getAddtasks(){
        return $this->view->getAddtasks();
    }

    function gettasksValidate(){
        return $this->model->gettasksValidate();
    }
    function getAddtasksSubmit(){
        return $this->model->getAddtasksSubmit();
    }
    function gettasksdetailList(){
        return $this->view->gettasksdetailList();
    }

    function gettasksdetail(){
        return $this->view->gettasksdetail();
    }

	
}