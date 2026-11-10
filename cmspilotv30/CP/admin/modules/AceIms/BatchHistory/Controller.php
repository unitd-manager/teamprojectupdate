<?
class CP_Admin_Modules_AceIms_BatchHistory_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getBulkUpdateEvaluate() {
        return $this->model->getBulkUpdateEvaluate();
    }

    function getBulkUpdateEvaluateSubmit() {
        return $this->model->getBulkUpdateEvaluateSubmit();
    }

    function getPrintVoucher() {
        return $this->model->getPrintVoucher();
    }
}