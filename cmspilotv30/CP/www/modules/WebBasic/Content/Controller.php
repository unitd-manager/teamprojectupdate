<?
class CP_Www_Modules_WebBasic_Content_Controller extends CP_Common_Lib_ModuleControllerAbstract {

    /**
     *
     */
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $hook = getCPModuleHook('webBasic_content', 'controller', '', $this);
        if($hook['status'] && $hook['returnVal'] !== false){
            return $hook['html'];
        }

        $text = '';
        if ($tv['secType'] == 'Site Search') {
            $pSiteSearch = getCPPluginObj('common_siteSearch');
            $text = $pSiteSearch->getView();

        } else if ($tv['secType'] == 'Testimonials' || $tv['catType'] == 'Testimonials' ) {
            $text = $this->getList('testimonialsList');

        } else if ($tv['secType'] == 'Press Release' || $tv['catType'] == 'Press Release' ) {
            if ($tv['record_id'] > 0){
                $text = $this->getDetail('pressDetail');
            } else {
                $text = $this->getList('pressList');
            }

        } else if ($tv['secType'] == 'Image Block Attachment' || $tv['catType'] == 'Image Block Attachment' ) {
            $text = $this->getList('imageBlockAttachmentList');

        } else if ($tv['secType'] == 'Thin List' || $tv['catType'] == 'Thin List') {
            if ($tv['record_id'] > 0){
                $text = $this->getDetail('detail');
            } else {
                $text = $this->getList('thinList');
            }

        } else if ($tv['secType'] == 'Tab Content' || $tv['catType'] == 'Tab Content') {
            $text = $this->getList('listAsTabs');


        } else if ($tv['secType'] == 'Accordian Content' || $tv['catType'] == 'Accordian Content') {
            $text = $this->getList('listAsAccordian');

        } else if ($tv['secType'] == 'List in Detail' || $tv['catType'] == 'List in Detail') {
            $text = $this->getList('listInDetail');

        } else if ($tv['secType'] == 'List Detail Combo' || $tv['catType'] == 'List Detail Combo') {
            $text = $this->getList('listDetailCombo');

        } else if ($tv['secType'] == 'Site Map' || $tv['catType'] == 'Site Map') {
            $text = $this->getList('siteMap');

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }

        return $text;
    }

    /**
     *
     */
    function getList($alternateListFn = '', $exp = array()){
        return parent::getList($alternateListFn, $exp);
    }

    /**
     *
     */
    function getDetail($alternateDetailFn = ''){
        return parent::getDetail($alternateDetailFn);
    }

    /**
     *
     */
    function getSpContent() {
        return $this->view->getSpContent();
    }

    /**
     *
     */
    function getFlipBookForm() {
        return $this->view->getFlipBookForm();
    }

    /**
     *
     */
    function getFlipBookFormSubmit() {
        return $this->view->getFlipBookFormSubmit();
    }

    /**
     *
     */
    function getFlipBookFormValidate() {
        return $this->view->getFlipBookFormValidate();
    }

    /**
     *
     */
    function getQuickTour() {
        return $this->view->getQuickTour();
    }

    /**
     *
     */
    function getResellers() {
        return $this->view->getResellers();
    }

    /**
     *
     */
    function getSpContentByType() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $contentType = $fn->getReqParam('ct', 'Record');
        $arr = $this->model->getRecordByType($contentType, false);
        return $arr;
    }
}