<?
class CP_Admin_Modules_Trading_Product_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getCalculatedValuesRfq() {
        return $this->model->getCalculatedValuesPoItems();
    }

    function getCalculatedValuesQuoteItems() {
        return $this->model->getCalculatedValuesQuoteItems();
    }

    function getCalculatedValuesPoItems() {
        return $this->model->getCalculatedValuesPoItems();
    }

    function getCalculatedValuesSoItems() {
        return $this->model->getCalculatedValuesSoItems();
    }

    function getTempImportProductImages() {
        return $this->model->getTempImportProductImages();
    }

    function getEditCostBreakdown() {
        return $this->view->getEditCostBreakdown();
    }

    function getSaveCostBreakdown() {
        return $this->model->getSaveCostBreakdown();
    }

    function getCalculateProductCosting() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $arr = $this->model->getCalculateProductCosting();
        $arr['markupArr'] = $this->model->getCalculateProductMarkup();

        return $cpUtil->getJsonFromArray($arr);
    }

    function getCalculateProductMarkup() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $arr = $this->model->getCalculateProductMarkup();
        return $cpUtil->getJsonFromArray($arr);
    }

    function getStartDataMigration() {
        return $this->model->getStartDataMigration();
    }

    function getExportForWeb() {
        return $this->model->getExportForWeb();
    }
    function getChooseConfirmedRFQForProduct() {
        return $this->model->getChooseConfirmedRFQForProduct();
    }
}