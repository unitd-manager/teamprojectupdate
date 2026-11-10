<?
include_once 'ImportDataHK.php';

class CP_Admin_Modules_Directory_Business_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    var $importHK;

    function __construct() {
        $this->importHK = new CP_Admin_Modules_Directory_Business_ImportDataHK();
    }
    function getLinkPictures() {
        return $this->model->linkPictures();
    }

    function getUpdateAreaDetailsByPolygon() {
        return $this->importHK->getUpdateAreaDetailsByPolygon();
    }

    function getDuplicateAndCloseBusiness() {
        return $this->model->getDuplicateAndCloseBusiness();
    }

    function getCloseBusiness() {
        return $this->model->getCloseBusiness();
    }

    function getBulkPromotionForm() {
        return $this->view->getBulkPromotionForm();
    }

    function getBulkPromotionSubmit() {
        return $this->model->getBulkPromotionSubmit();
    }

    function getBulk3rdPartyPromotionForm() {
        return $this->view->getBulk3rdPartyPromotionForm();
    }

    function getBulk3rdPartyPromotionSubmit() {
        return $this->model->getBulk3rdPartyPromotionSubmit();
    }

    function getImportBusinessHours() {
        return $this->model->importBusinessHours();
    }

    /**
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_business&_spAction=importDataUK
     */
    function getImportDataUK() {
        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        include_once 'ImportDataUK.php';
        $import = new CP_Admin_Modules_Directory_Business_ImportDataUK();
        $import->importData();
    }

    /**
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_business&_spAction=importTags
     */
    function getImportTags() {
        die('disabled');
        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        include_once 'ImportDataUK.php';
        $import = new CP_Admin_Modules_Directory_Business_ImportDataUK();
        $import->importTags();
    }

    /**
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_business&_spAction=importPhotosHK
     */
    function getImportPhotosHK() {
        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        $this->importHK->getImportPhotos();
    }

    /**
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_business&_spAction=importBuildingsHK
     */
    function getImportBuildingsHK() {
        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        $this->importHK->getImportBuildings();
    }

    /**
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_business&_spAction=importRestaurantsHK
     */
    function getImportRestaurantsHK() {
        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        $this->importHK->getImportRestaurants();
    }

    function getScrapSocialMediaUrls() {
        $fn = Zend_Registry::get('fn');

        $business_id = $fn->getReqParam('record_id');

        print $this->model->getScrapSocialMediaUrls($business_id);
    }

    /**
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_business&_spAction=scrapSocialMediaUrlsMulti&showHTML=0
     */
    function getScrapSocialMediaUrlsMulti() {
        $this->model->getScrapSocialMediaUrlsMulti();
    }

    /**
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_business&_spAction=addWatermarkBulkPre&showHTML=0
     */
    function getAddWatermarkBulkPre() {
        $this->model->getAddWatermarkBulkPre();
    }
}