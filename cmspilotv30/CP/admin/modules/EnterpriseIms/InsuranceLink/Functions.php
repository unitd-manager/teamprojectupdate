<?
class CP_Admin_Modules_EnterpriseIms_InsuranceLink_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enterpriseIms_insuranceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'student_insurance'
           ,'keyField'  => 'student_insurance_id'
        ));
    }
}
