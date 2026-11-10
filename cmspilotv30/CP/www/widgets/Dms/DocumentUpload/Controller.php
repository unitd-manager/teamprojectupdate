<?
class CP_Www_Widgets_Dms_DocumentUpload_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $formAction = '/index.php?widget=dms_documentUpload&_spAction=uploadSubmit&showHTML=0';
    var $returnUrl  = '';
    var $module     = 'dms_document';
    var $recordType = 'attachment';
    var $record_id  = '';

    function getUploadSubmit() {
        return $this->model->getUploadSubmit();
    }
}