<?
class CP_Admin_Modules_EnggCrm_CompanyLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_companyLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'company'
           ,'keyField'  => 'company_id'
        ));
    }
}
