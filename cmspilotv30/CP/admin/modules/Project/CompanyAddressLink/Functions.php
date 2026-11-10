<?
class CP_Admin_Modules_Project_CompanyAddressLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_companyAddressLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'company_address'
           ,'keyField'      => 'company_address_id'
        ));
    }
}
