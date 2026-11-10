<?
class CP_Common_Plugins_Common_Message_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    var $roomName      = '';
    var $recordId      = '';
    var $contactModule = '';
    var $allowEdit     = false;
    var $allowDelete   = false;
    var $heading       = '';
    var $addReviewLbl  = '';
    var $nameRequired  = false;
    var $emailRequired = false;
    var $showStarRating = false;
    var $showSubject = false;
    var $showCaptcha = false;
    var $formClass   = 'yform columnar';

    var $scrollContent   = false;
    var $scrollSpeed     = 1;
    var $scrollDirection = 'vertical';
    var $scrollAutoMode  = 'loop'; //loop, off, bounce
    var $scrollClass     = 'simply-scroll-vert'; // simply-scroll-vert, simply-scroll
    var $scrollHandle    = 'simplyScroll1';

    /**
     *
     */
    function getAdd(){
        return $this->model->getAdd();
    }

    /**
     *
     */
    function getDelete() {
        return $this->model->getDelete();
    }

    /**
     *
     */
    function getEdit() {
        return $this->view->getEdit();
    }

    /**
     *
     */
    function getSave(){
        return $this->model->getSave();
    }
}