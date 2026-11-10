<?
class CP_Admin_Modules_EnggCrm_CompanyAddressLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_companyAddressLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'company_address'
           ,'keyField'      => 'company_address_id'
        ));
    }
}
