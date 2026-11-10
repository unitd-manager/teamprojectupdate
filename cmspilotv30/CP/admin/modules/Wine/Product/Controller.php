<?
class CP_Admin_Modules_Wine_Product_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getImportFromJDE(){
        return $this->model->getImportFromJDE();
    }
    /**
     * 
     * @return type
     */
    function getWipeProductForImport(){
        return $this->model->getWipeProductForImport();
    }
}