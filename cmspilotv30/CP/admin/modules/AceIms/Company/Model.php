<?
class CP_Admin_Modules_AceIms_Company_Model extends CP_Common_Modules_AceIms_Company_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the company name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the company name');
        $validate->validateData('reg_number', 'Please enter the registration number');
        $validate->validateData('category', 'Please select type of registration');
        $validate->validateData('phone', 'Please enter phone no.');
        $validate->validateData('email', 'Please enter company email');

        $validate->validateData('address1', 'Please enter address 1');
        $validate->validateData('address_country_code', 'Please select the country');
        $validate->validateData('address_po_code', 'Please enter the postal code');

        $validate->validateData('contact_name', 'Please enter the contact person name');
        $validate->validateData('contact_phone', 'Please enter contact person no.');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'reg_number');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'email');

        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');

        $fa = $fn->addToFieldsArray($fa, 'contact_name');
        $fa = $fn->addToFieldsArray($fa, 'contact_phone');
        $fa = $fn->addToFieldsArray($fa, 'contact_mobile');
        $fa = $fn->addToFieldsArray($fa, 'contact_email');
        $fa = $fn->addToFieldsArray($fa, 'contact_position');

        return $fa;
    }
    /**
     *
     */
    function getAceImsCompanyAceImsOrderLinkSQL($id) {

        $SQL = "
        SELECT a.order_id
              ,a.order_date
        FROM `order` a
        WHERE a.company_id = '{$id}'
        ORDER BY a.order_date
        ";

        return $SQL;
    }

    /**
     *
     */
    function getAceImsCompanyAceImsContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.email
              ,a.registration_no
              ,a.id_card_no
              ,a.phone
              ,a.mobile
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";

    }

    /**
     *
     */
    function getCompanyAddSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getCompanyAddValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'reg_number');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'email');

        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');

        $company_id = $fn->addRecord($fa, 'company');

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCompanyAddValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter company name');
        $validate->validateData('reg_number' , 'Please enter business reg. No.');

        $reg_number = $fn->getPostParam('reg_number', '', true);

        if ($reg_number != ''){
            $rec = $fn->getRecordByCondition('company', "reg_number = '{$reg_number}'");
            $expEmail = array('displayText' => $reg_number,  'target' => '_blank');
            $emailLink = $fn->getRecordDetailLink('aceIms_company', 'record_id', $rec['company_id'], $expEmail);

            if (is_array($rec)){
                $validate->errorArray['reg_number']['name'] = "reg_number";
                $validate->errorArray['reg_number']['msg']  = "Company already registered. '{$emailLink}'";

            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
