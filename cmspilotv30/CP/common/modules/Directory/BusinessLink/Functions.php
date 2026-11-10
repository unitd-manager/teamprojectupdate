<?
class CP_Common_Modules_Directory_BusinessLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $fn = Zend_Registry::get('fn');
        $srcRoom = $fn->getReqParam('srcRoom');
        $spAction = $fn->getReqParam('_spAction');
        $modObj = $modules->getModuleObj('directory_businessLink');
        
        $tbl = 'business';
        $fld = 'business_id';

        if ($srcRoom == 'directory_businessContact' && ($spAction == 'edit' || $spAction == 'save')){
            $tbl = 'business_contact_link';
            $fld = 'business_contact_link_id';
        }
        
        $modules->registerModule($modObj, array(
            'tableName' => $tbl
           ,'keyField'  => $fld
        ));
    }
}
