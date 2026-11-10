<?
class CP_Admin_Modules_Accountsg_AccHead_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getAccountHeadsAsJSON() {
        $accHeadsArr = $this->model->getAccountHeadsAsArray();
        $accHeadsJSON = $this->view->getAccountHeadsAsJSON($accHeadsArr);

        return $accHeadsJSON;
    }

    function getAccHeadDetails() {
        $cpUtil = Zend_Registry::get('cpUtil');
        return $cpUtil->getJsonFromArray($this->model->getAccHeadDetails());
    }

}