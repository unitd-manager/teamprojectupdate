<?
class CP_Www_Modules_Edukloud_Task_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getNewTask() {
        return $this->view->getNewTask();
    }

    function getNewTaskSubmit() {
        return $this->model->getNewTaskSubmit();
    }

    function getEditTask() {
        return $this->view->getEditTask();
    }

    function getEditTaskSubmit() {
        return $this->model->getEditTaskSubmit();
    }

}
