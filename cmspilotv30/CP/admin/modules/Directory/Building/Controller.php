<?
class CP_Admin_Modules_Directory_Building_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getUpdateLatLng(){
        return $this->model->getUpdateLatLng();
    }

    function getUpdateLatLngBulk(){
        return $this->model->getUpdateLatLngBulk();
    }

    function getCalculateNearestTransportLink(){
        return $this->model->getCalculateNearestTransportLink();
    }
}