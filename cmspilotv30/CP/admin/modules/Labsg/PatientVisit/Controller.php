<?
class CP_Admin_Modules_Labsg_PatientVisit_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getUpdateProductLineItems() {
        return $this->model->getUpdateProductLineItems();
    }

    function getTreatmentRecordSubmit() {
        return $this->model->getTreatmentRecordSubmit();
    }

    function getSearchList(){
        return $this->view->getSearchList();
    }

    function getPatientVisitSearchResult(){
        return $this->view->getPatientVisitSearchResult();
    }

    function getPatientVisitAppointmentSearchResult(){
        return $this->view->getPatientVisitAppointmentSearchResult();
    }

    function getSelectDoctorDetails(){
        return $this->view->getSelectDoctorDetails();
    }

    function getCreateVisitRecordSubmit(){
        return $this->model->getCreateVisitRecordSubmit();
    }

    function getCreateVisitRecordDirect(){
        return $this->model->getCreateVisitRecordDirect();
    }

    function getAddNoteTreatment() {
        return $this->view->getAddNoteTreatment();
    }

    function getAddNoteTreatmentSubmit() {
        return $this->model->getAddNoteTreatmentSubmit();
    }

    function getSummaryPortalSubmit() {
        return $this->model->getSummaryPortalSubmit();
    }

    function getCreateOrder() {
        return $this->model->getCreateOrder();
    }

    function getCreateOrderIndividual() {
        return $this->model->getCreateOrderIndividual();
    }

    function getUpdateConsultingFees() {
        return $this->model->getUpdateConsultingFees();
    }

    function getConvertFollowUpDate() {
        return $this->model->getConvertFollowUpDate();
    }

    function getAddPatientRecord() {
        return $this->view->getAddPatientRecord();
    }

    function getAddPatientRecordSubmit() {
        return $this->model->getAddPatientRecordSubmit();
    }

    function getSummaryInOrder() {
        return $this->view->getSummaryInOrder();
    }

    function getPrintLabel() {
        return $this->view->getPrintLabel();
    }

    function getCompanyNameJSON() {
        return $this->view->getCompanyNameJSON();
    }

    function getPrintLabelPatientVisitForm() {
        return $this->view->getPrintLabelPatientVisitForm();
    }

    function getPrintLabelPatientVisitFormSubmit() {
        return $this->model->getPrintLabelPatientVisitFormSubmit();
    }

    function getAddNewValuelistForm() {
        return $this->view->getAddNewValuelistForm();
    }

    function getAddNewValuelistFormSubmit() {
        return $this->model->getAddNewValuelistFormSubmit();
    }

    function getValueByValuelistJSON() {
        return $this->model->getValueByValuelistJSON();
    }

    function getCancelPatientVisitForm() {
        return $this->view->getCancelPatientVisitForm();
    }

    function getCancelPatientVisitFormSubmit() {
        return $this->model->getCancelPatientVisitFormSubmit();
    }
}