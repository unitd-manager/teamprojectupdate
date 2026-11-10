<?
class CP_Admin_Modules_Common_Broadcast_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getSendBroadcast() {
        return $this->model->getSendBroadcast();
    }

    /**
     *
     */
    function getSendTestBroadcast() {
        return $this->model->getSendTestBroadcast();
    }
}