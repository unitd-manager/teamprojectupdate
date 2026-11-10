<?
class CPL_Admin_Modules_Core_UserGroup_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getCreateAccessRecords(){
        return $this->model->getCreateAccessRecords();
    }

    function getCreateWidgetAccessRecords(){
    	return $this->model->getCreateWidgetAccessRecords();
    }
}