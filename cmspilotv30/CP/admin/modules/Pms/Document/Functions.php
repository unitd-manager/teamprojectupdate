<?
class CP_Admin_Modules_Pms_Document_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_document');
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
        $mediaObj = $mediaArr->getMediaObj('pms_document', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pms_document', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pms_document', 'pms_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'document_history'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'historyTableKeyField'  => 'document_history_id'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pms_document', 'core_staffLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'document_history'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'historyTableKeyField'  => 'document_history_id'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pms_document', 'pms_courseLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'document_course'
           ,'displayTitleFieldName' => "a.title"
           ,'historyTableKeyField'  => 'document_course_id'
        ));
		
    }
}