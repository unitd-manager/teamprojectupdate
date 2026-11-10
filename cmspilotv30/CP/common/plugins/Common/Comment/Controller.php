<?
class CP_Common_Plugins_Common_Comment_Controller extends CP_Common_Lib_PluginControllerAbstract
{
    var $roomName      = '';
    var $recordId      = '';
    var $contactId     = '';
    var $contactModule = '';
    var $allowAdd      = true;
    var $allowEdit     = false;
    var $allowDelete   = false;
    var $heading       = '';
    var $addReviewLbl  = '';
    var $nameRequired  = false;
    var $emailRequired = false;
    var $showStarRating = false;
    var $showSubject = false;
    var $showCaptcha = false;
    var $showContactPic = false;
    var $formClass   = 'yform columnar';
    var $allowEditName = false;
    var $loginRequiredToPost = false;
    var $loginRequiredText = '';

    var $scrollContent   = false;
    var $scrollFrameRate = 20;
    var $scrollSpeed     = 1;
    var $scrollDirection = 'vertical';
    var $scrollAutoMode  = 'loop'; //loop, off, bounce
    var $scrollClass     = 'simply-scroll-vert'; // simply-scroll-vert, simply-scroll
    var $scrollHandle    = 'simplyScroll1';
    var $hasFileUpload   = false;
    var $displayCommentPics = false;
    var $hasReportAbuse = false;
    var $showNotifyCbox = false;
    var $notifyAdmin    = false;
    var $publishByDefault = true;

    /**
     *
     */
    function getView($exp = array()){
        if (CP_SCOPE == 'www'){
            $ln = Zend_Registry::get('ln');
            $this->contactModule = 'common_contact';
            $this->heading = ($this->heading !='') ? $this->heading : $ln->gd('p.common.comment.heading');
            $this->addReviewLbl = ($this->addReviewLbl !='') ? $this->addReviewLbl : $ln->gd('p.common.comment.lbl.addReview');
            $this->nameRequired = true;
            $this->emailRequired = true;
        } else {
            $this->contactModule = 'core_staff';
            $this->allowEdit   = true;
            $this->allowDelete = true;
            $this->heading     = 'Notes / Comments';
            $this->addReviewLbl = 'Add a note';
        }
        
        return parent::getView($exp);
    }

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

    //==================================================================//
    function getReportAbuse(){
        $theme = Zend_Registry::get('currentTheme');
        $fn = Zend_Registry::get('fn');
        
        $text = '';
        if (isLoggedInWww()){
            $id = $fn->getReqParam('id');
            $text = $this->model->getReportAbuse($id, $_SESSION['cpContactId']);
        }
        
        return $text;
    }

    //==================================================================//
    function getLikeComment(){
        return $this->model->getLikeComment();
    }

    //==================================================================//
    function getSpecialRatingDisplay(){
        $fn = Zend_Registry::get('fn');
        $comment_id = $fn->getReqParam('id', '', true);
        return $this->view->getSpecialRatingDisplay($comment_id);
    }

    //==================================================================//
    function getSaveSpecialRating(){
        return $this->model->getSaveSpecialRating();
    }

    //==================================================================//
    function getList(){
        $fn = Zend_Registry::get('fn');
        $modName = $fn->getReqParam('modName', '', true);
        $record_id = $fn->getReqParam('record_id', '', true);
        return $this->model->getDataArrayTwig($modName, $record_id);
    }

    //==================================================================//
    function getReply(){
        $fn = Zend_Registry::get('fn');
        $parent_id = $fn->getReqParam('parent_id', '', true);
        $record_id = $fn->getReqParam('record_id', '', true);

        $rec = $fn->getRecordRowByID('comment', 'comment_id', $parent_id);
        
        if (is_array($rec)){
            return $rec;
        }
    }

    //==================================================================//
    function getForm(){
        $fn = Zend_Registry::get('fn');
        $record_id = $fn->getReqParam('id', '', true);

        $params['showUpload'] = true;
        $params['showSpRating'] = true;
        $params['room_name'] = 'directory_business';
        $params['record_id'] = $record_id;
        
        return $params;
    }
}