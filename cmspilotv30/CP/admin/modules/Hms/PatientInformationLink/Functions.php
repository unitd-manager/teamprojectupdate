<?
class CP_Admin_Modules_Hms_PatientInformationLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('hms_patientInformationLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'patient_relationinfo'
           ,'keyField'  => 'patient_information_source_id'
        ));        
    }
}
