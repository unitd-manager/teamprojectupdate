<?
class CP_Admin_Modules_Pos_SubCategory_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getBulkMoveToCategory(){
        return $this->view->getBulkMoveToCategoryForm();
    }

    function getSubCategoryByCategoryJSON(){
        return $this->view->getSubCategoryByCategoryJSON();
    }
    
    function getBulkMoveToCategorySubmit(){
        return $this->model->getBulkMoveToCategorySubmit();
    }
}