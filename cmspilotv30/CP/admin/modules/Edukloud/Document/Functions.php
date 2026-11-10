<?
class CP_Admin_Modules_Edukloud_Document_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_document');
        $modObj['tableName'] = 'document';
        $modObj['keyField']  = 'document_id';
        $modules->registerModule($modObj, array(
            'title' => 'Document'
           ,'hasFlagInList' => 0
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukloud_document', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukloud_document', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukloud_document', 'edukloud_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'document_history'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'historyTableKeyField'  => 'document_history_id'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukloud_document', 'core_staffLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'document_history'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'historyTableKeyField'  => 'document_history_id'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukloud_document', 'edukloud_courseLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'document_course'
           ,'displayTitleFieldName' => "a.title"
           ,'historyTableKeyField'  => 'document_course_id'
        ));
		
    }
}