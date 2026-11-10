<?
class CP_Www_Modules_EdukiteWeb_Task_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getUploadTaskSubmit() {
        return $this->model->getUploadTaskSubmit();
    }

    function getMyHomework() {
        return $this->view->getMyHomework();
    }

    function getAddCommentSubmit() {
        return $this->model->getAddCommentSubmit();
    }

    function getDisplayComment() {
        return $this->view->getDisplayComment();
    }

}