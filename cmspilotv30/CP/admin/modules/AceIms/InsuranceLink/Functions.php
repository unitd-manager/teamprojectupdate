<?
class CP_Admin_Modules_AceIms_InsuranceLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_insuranceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'student_insurance'
           ,'keyField'  => 'student_insurance_id'
        ));
    }
}
