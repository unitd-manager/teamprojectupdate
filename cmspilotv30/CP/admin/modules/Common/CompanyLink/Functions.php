<?
class CP_Admin_Modules_Common_CompanyLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('common_companyLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'company'
           ,'keyField'  => 'company_id'
        ));
    }
}
