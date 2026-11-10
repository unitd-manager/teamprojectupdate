<?
class CPL_Admin_Modules_Project_Task_Controller extends CP_Admin_Modules_Project_Task_Controller
{
    /**
     *
     */
    function getTaskJsonByProId() {
        return $this->model->getTaskJsonByProId();
    }    
}
