<?
class CP_Admin_Modules_Project_InvoiceLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_invoiceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'invoice'
           ,'keyField'  => 'invoice_id'
        ));
    }
}
