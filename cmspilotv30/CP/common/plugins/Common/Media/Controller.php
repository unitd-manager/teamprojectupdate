<?
class CP_Common_Plugins_Common_Media_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    /**
     *
     */
    function getRightPanelMediaDisplay($displayTitle = "", $module, $recordType, $row = "", $exp = array()) {
        return $this->view->getRightPanelMediaDisplay($displayTitle, $module, $recordType, $row, $exp);
    }

    /**
     *
     */
    function getSelectMedia($module='', $recordType='', $id='') {
        return $this->view->getSelectMedia($module, $recordType, $id);
    }

    /**
     *
     */
    function getAddMedia($module = '', $recordType = '', $id = '', $frmFieldName = '', $exp = array()) {
        $fn = Zend_Registry::get('fn');

        if ($module == ''){
            $module = $fn->getReqParam('room');
            $recordType = $fn->getReqParam('recordType');
            $id = $fn->getReqParam('id');
            $frmFieldName = $fn->getReqParam('fileFldName', 'fileName');
        }

        $fileFldName = ($frmFieldName != '') ? $frmFieldName : 'fileName';

        return $this->model->getAddMedia($module, $recordType, $id, $fileFldName, $exp);
    }

    /**
     *
     */
    function deleteMediaRecord($module, $keyFieldID, $recordType = '') {
        return $this->model->deleteMediaRecord($module, $keyFieldID, $recordType);
    }

    /**
     *
     */
    function getSaveMedia() {
        return $this->model->getSaveMedia();
    }

    /**
     *
     */
    function getDeleteMedia($media_id = '') {
        return $this->model->getDeleteMedia($media_id);
    }

    /**
     *
     */
    function getEditMediaProperties() {
        return $this->view->getEditMediaProperties();
    }

    /**
     *
     */
    function getEditProperty() {
        $fn = Zend_Registry::get('fn');
        $media_id = $fn->getReqParam('mid');
        $row = $fn->getRecordRowByID('media', 'media_id', $media_id);

        return $row;
    }

    /**
     *
     */
    function getSaveMediaProperties() {
        return $this->model->getSaveMediaProperties();
    }


    function getEditMediaPropertiesValidate() {
        return $this->model->getEditMediaPropertiesValidate();
    }

    function getEditImageCaption() {
        return $this->view->getEditImageCaption();
    }

    function getSaveImageCaption() {
        return $this->model->getSaveImageCaption();
    }

    function getCropMediaForm() {
        return $this->view->getCropMediaForm();
    }

    function getSaveCroppedImage() {
        return $this->model->getSaveCroppedImage();
    }

    /**
     *
     */
    function getMediaFilesDisplay($module = '', $recordType = '', $id = '', $exp = array()){
        return $this->view->getMediaFilesDisplay($module, $recordType, $id, $exp);
    }

    /**
     *
     */
    function getMediaFilesDisplayThin($module = '', $recordType = '', $id = ''){
        return $this->view->getMediaFilesDisplayThin($module, $recordType, $id);
    }

    /**
     *
     * @param <type> $module
     * @param <type> $keyFieldName
     * @param <type> $id
     * @param <type> $recordType
     * @return <type>
     */
    function getMediaFilesArray($module, $recordType, $id, $exp = array()){
        return $this->model->getMediaFilesArray($module, $recordType, $id, $exp);
    }

    /**
     *
     */
    function getMediaPicture($module, $recordType, $id, $extraParam = array()){
        return $this->view->getMediaPicture($module, $recordType, $id, $extraParam);
    }

    /**
     *
     */
    function getFirstMediaRecord($module, $recordType, $id, $langForced = ''){
        return $this->model->getFirstMediaRecord($module, $recordType, $id, $langForced);
    }

    /**
     *
     */
    function getFirstFileName($module = '', $recordType = '', $id = ''){
        $fn = Zend_Registry::get('fn');

        if ($module == ''){
            $module = $fn->getReqParam('room');
            $recordType = $fn->getReqParam('recordType');
            $id = $fn->getReqParam('id');
        }

        return $this->model->getFirstFileName($module, $recordType, $id);
    }

    /**
     *
     */
    function getDuplicateMedia($module, $id, $newRecordId){
        return $this->model->getDuplicateMedia($module, $id, $newRecordId);
    }

    /**
     *
     */
    function getSingleFileForLinkGrid($module = '', $recordType = '', $id = ''){
        $fn = Zend_Registry::get('fn');

        if ($module == ''){
            $module = $fn->getReqParam('room');
            $recordType = $fn->getReqParam('recordType');
            $id = $fn->getReqParam('id');
        }

        $mediaExp = array('showDeleteIcon' => true, 'folder' => 'thumb');
        return $this->view->getMediaPicture($module, $recordType, $id, $mediaExp);
    }

}