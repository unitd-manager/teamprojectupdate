<?
class CP_Admin_Modules_WebBasic_TabContentLink_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('webBasic_tabContentLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'tab_content'
           ,'keyField'      => 'tab_content_id'
        ));
    }

    /**
     *
     */
    function getContentRecordTypeArray(){
        $arr =
        array(
             "Record"
        );

        return $arr;
    }
}
