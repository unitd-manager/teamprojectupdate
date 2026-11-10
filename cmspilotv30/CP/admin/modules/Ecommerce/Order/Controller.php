<?
class CP_Admin_Modules_Ecommerce_Order_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getUploadToDHL() {
        return $this->model->getUploadToDHL();
    } 
    
    function getGenerateDHLLabel() {
        return $this->model->getGenerateDHLLabel();
    }     
    
    function getAttachDHLLabelToOrder() {
        return $this->model->getAttachDHLLabelToOrder();
    }     
}