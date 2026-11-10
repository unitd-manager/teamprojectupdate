<?
class CP_Www_Themes_Quest_Hooks_WidgetEcommerceShippingDetails
{

    /**
     *
     */
    function getRowsHTML($viewObj) {

        if ($_SESSION['cpLoginTypeWWW'] == 'pms_company'){
            return $this->getCompanyDetails($viewObj);
        }

        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$viewObj->controller;
        $viewHelper = Zend_Registry::get('viewHelper');
        $cpUrl = Zend_Registry::get('cpUrl');

        $fieldset = '';
        $row = array();

        $dataArray = $viewObj->model->dataArray;
        if(count($dataArray) > 0){
            $row = $dataArray[0];
        }

        $expCourseArr = array('condn' => 'published = 1');

        $sqlCourse        = $fn->getDDSql('pms_course', $expCourseArr);
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');
        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlLanguage      = $fn->getValueListSQL('language');
        $sqlQual          = $fn->getValueListSQL('educationalQualification');
        $sqlCountry       = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlSalaryRange   = $fn->getValueListSQL('salaryRange');

        $expVL = array('sqlType' => 'OneField');
        $formObj->mode = $c->mode;

        $courseId = $fn->getIssetParam($row,'course_id');
        $expCourse = array();
        $attrForCourseDD = '';
        if ($courseId > 0){
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $courseId);
            $expCourse = array('detailValue' => $courseRec['title']);
            $attrForCourseDD = " course_id='{$courseId}'";
        }

        $scheduleUrl = $cpUrl->getUrlByCatType('Accordian Content', 'Content');

        $fieldset1 = "
        <div class='courseWrapperStudent'{$attrForCourseDD}>
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.course.lbl'), 'course_id', $sqlCourse, $fn->getIssetParam($row,'course_id'), $expCourse)}
        </div>
        <div class='wsqOnly language'>
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.language.lbl'), 'course_language', $sqlLanguage, $fn->getIssetParam($row,'course_language'), $expVL)}
        </div>
        {$formObj->getDateRow($ln->gd('cp.form.fld.trainingDate.lbl'), 'course_training_date', $fn->getIssetParam($row,'course_training_date'))}
        <a href='{$scheduleUrl}' target='_blank'>{$ln->gd('cp.form.fld.clickForSchedule.lbl')}</a>
        ";

        //{$formObj->getDDRowBySQL($ln->gd('cp.form.fld.maritalStatus.lbl'), 'marital_status', $sqlMaritalStatus, $fn->getIssetParam($row,'marital_status'), $expVL)}
        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name', $fn->getIssetParam($row,'first_name'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name', $fn->getIssetParam($row,'last_name'))}
        <div class='wsqOnly'>
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.gender.lbl'), 'gender', $sqlGender, $fn->getIssetParam($row,'gender'), $expVL)}
        </div>
        {$formObj->getTBRow($ln->gd('cp.form.fld.idCardNo.lbl'), 'id_card_no', $fn->getIssetParam($row,'id_card_no'))}
        <div class='wsqOnly'>
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.nationality.lbl'), 'nationality', $sqlNationality, $fn->getIssetParam($row,'nationality'), $expVL)}
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.race.lbl'), 'race', $sqlRace, $fn->getIssetParam($row,'race'), $expVL)}
            {$formObj->getDateRow($ln->gd('cp.form.fld.dateOfBirth.lbl'), 'date_of_birth', $fn->getIssetParam($row,'date_of_birth'), array('yearStart' => 1920, 'yearEnd' => date('Y') - 10))}
        </div>
        ";

        /*{$formObj->getTBRow($ln->gd('cp.form.fld.emergencyContactName.lbl'), 'emergency_contact_name', $fn->getIssetParam($row,'emergency_contact_name'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.emergencyContactMobile.lbl'), 'emergency_contact_mobile', $fn->getIssetParam($row,'emergency_contact_mobile'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.emergencyContactOfficeNo.lbl'), 'emergency_contact_office_no', $fn->getIssetParam($row,'emergency_contact_office_no'))}*/
        
        $fieldset3 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.mobile.lbl'), 'mobile', $fn->getIssetParam($row,'mobile'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone', $fn->getIssetParam($row,'phone'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', $fn->getIssetParam($row,'email'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.address1.lbl'), 'address_flat', $fn->getIssetParam($row,'address_flat'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'address_street', $fn->getIssetParam($row,'address_street'))}
        {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'address_country_code', $sqlCountry, $fn->getIssetParam($row,'address_country_code', 'SG'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.poCode.lbl'), 'address_po_code', $fn->getIssetParam($row,'address_po_code'))}
        ";

        //{$formObj->getTBRow($ln->gd('cp.form.fld.city.lbl'), 'address_city', $fn->getIssetParam($row,'address_city'))}
        $fieldset4 = "
        <div class='wsqOnly'>
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.schoolHighestQualification.lbl'), 'school_highest_qual', $sqlQual, $fn->getIssetParam($row,'school_highest_qual'), $expVL)}
            {$formObj->getTBRow($ln->gd('cp.form.fld.companyName.lbl'), 'company_name', $fn->getIssetParam($row,'company_name'))}
            {$formObj->getTBRow($ln->gd('cp.form.fld.designation.lbl'), 'position', $fn->getIssetParam($row,'position'))}
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.salaryRange.lbl'), 'salary_range', $sqlSalaryRange, $fn->getIssetParam($row,'salary_range'), $expVL)}
        </div>
        ";

        /*$fieldset5 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.schoolName.lbl'), 'school_name', $fn->getIssetParam($row,'school_name'))}
        {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.schoolCountry.lbl'), 'school_country', $sqlCountry, $fn->getIssetParam($row,'address_country_code', $c->defaultCountryCode))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.schoolFrom.lbl'), 'school_from', $fn->getIssetParam($row,'school_from'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.schoolTo.lbl'), 'school_to', $fn->getIssetParam($row,'school_to'))}
        ";

        $fieldset6 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.companyROCNo.lbl'), 'company_roc_no', $fn->getIssetParam($row,'company_roc_no'))}
        {$formObj->getTARow($ln->gd('cp.form.fld.address.lbl'), 'company_address', $fn->getIssetParam($row,'company_address'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.poCode.lbl'), 'company_po_code', $fn->getIssetParam($row,'company_po_code'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.contactNo.lbl'), 'company_phone', $fn->getIssetParam($row,'company_phone'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'company_fax', $fn->getIssetParam($row,'company_fax'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.yrExperience.lbl'), 'yr_of_exp', $fn->getIssetParam($row,'yr_of_exp'))}
        {$formObj->getYesNoRRow($ln->gd('cp.form.fld.applyForSDF.lbl'), 'apply_for_sdf', $fn->getIssetParam($row,'apply_for_sdf'))}
        ";*/

        /*{$formObj->getFieldSetWrapped($ln->gd('w.ecommerce.shippingDetails.form.lgnd.educationalQualification'), $fieldset5)}
        {$formObj->getFieldSetWrapped($ln->gd('w.ecommerce.shippingDetails.form.lgnd.employmentDetails'), $fieldset6)}*/


        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('w.ecommerce.shippingDetails.form.lgnd.registerForTraining'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('w.ecommerce.shippingDetails.form.lgnd.personalParticulars'), $fieldset2)}
        {$formObj->getFieldSetWrapped($ln->gd('w.ecommerce.shippingDetails.form.lgnd.contactDetails'), $fieldset3)}
        <div class='wsqOnly'>
            {$formObj->getFieldSetWrapped($ln->gd('cp.form.lgnd.others'), $fieldset4)}
        </div>
        <div class='acknowledge'><input type='checkbox' name='agree_condition' id='fld_agree_condition' value='1' >
            <strong>{$ln->gd('acknowledgement')}</strong>
        </div>
        {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
        <input type='hidden' name='apply' value='0' />
        ";

        return $text;
    }

    /**
     *
     */
    function getCompanyDetails($viewObj) {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$viewObj->controller;
        $viewHelper = Zend_Registry::get('viewHelper');

        $fieldset = '';
        $row = array();

        $dataArray = $viewObj->model->dataArray;
        if(count($dataArray) > 0){
            $row = $dataArray[0];
        }

        $sqlCourse        = $fn->getDDSql('pms_course');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');
        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlLanguage      = $fn->getValueListSQL('language');
        $sqlQual          = $fn->getValueListSQL('educationalQualification');
        $sqlCountry       = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlSalaryRange   = $fn->getValueListSQL('salaryRange');

        $expVL = array('sqlType' => 'OneField');
        $formObj->mode = $c->mode;

        $courseId = $fn->getIssetParam($row,'course_id');
        $expCourse = array();
        if ($courseId > 0){
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $courseId);
            $expCourse = array('detailValue' => $courseRec['title']);
        }
        
        $themeObj = getCPThemeObj('Quest');

        $fieldset1 = "
        {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.course.lbl'), 'course_id', $sqlCourse, $fn->getIssetParam($row,'course_id'), $expCourse)}
        {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.language.lbl'), 'course_language', $sqlLanguage, $fn->getIssetParam($row,'course_language'), $expVL)}
        {$formObj->getDateRow($ln->gd('cp.form.fld.trainingDate.lbl'), 'course_training_date', $fn->getIssetParam($row,'course_training_date'))}
        ";

        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.companyName.lbl'), 'title', $fn->getIssetParam($row,'title'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.companyROCNo.lbl'), 'reg_number', $fn->getIssetParam($row,'reg_number'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.address1.lbl'), 'address1', $fn->getIssetParam($row,'address1'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'address2', $fn->getIssetParam($row,'address2'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.poCode.lbl'), 'address_po_code', $fn->getIssetParam($row,'address_po_code'))}
        {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'address_country_code', $sqlCountry, $fn->getIssetParam($row,'address_country_code', $c->defaultCountryCode))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone', $fn->getIssetParam($row,'phone'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'fax', $fn->getIssetParam($row,'fax'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.natureOfBusiness.lbl'), 'nature_of_business', $fn->getIssetParam($row,'nature_of_business'))}
        ";

        $fieldset3 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name', $fn->getIssetParam($row,'first_name'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name', $fn->getIssetParam($row,'last_name'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', $fn->getIssetParam($row,'email'))}
        ";

       /* $fieldset4 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.city.lbl'), 'address_city', $fn->getIssetParam($row,'address_city'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.name.lbl'), 'contact_name', $fn->getIssetParam($row,'contact_name'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'contact_email', $fn->getIssetParam($row,'contact_email'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.designation.lbl'), 'contact_position', $fn->getIssetParam($row,'contact_position'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.contactNo.lbl'), 'contact_phone', $fn->getIssetParam($row,'contact_phone'))}
        ";*/

        $trainees = '';
        $count = 1;
        if (isset($_SESSION['shippingDetails'])){
            $traineeData = @$_SESSION['shippingDetails']['traineeData'];

            if (is_array($traineeData)){
                foreach ($traineeData AS $traineeRow){
                    $trainees .= $themeObj->fns->getTraineeRow($traineeRow, $c->mode, $count);
                    $count++;
                }
            }
        }

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('cp.form.lgnd.companyDetails'), $fieldset2)}
        {$formObj->getFieldSetWrapped($ln->gd('w.ecommerce.shippingDetails.form.lgnd.authorizedRep'), $fieldset3)}
        <h3>{$ln->gd('w.ecommerce.shippingDetails.form.lgnd.trainees')}</h3>
        <div id='trainees'>
            {$trainees}
        </div>
        <div class='floatbox mt10 mb20'>
            <a class='button' id='btnAddTrainee' href='#'>{$ln->gd('w.ecommerce.shippingDetails.form.btn.addMoreTrainee')}</a>
        </div>
        <div class='acknowledge'><input type='checkbox' name='agree_condition' id='fld_agree_condition' value='1' >
        <strong>{$ln->gd('acknowledgement')}</strong>
        </div>
        <input type='hidden' name='apply' value='0' />
        {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');

        if ($memberType == 'pms_company'){
            $validate->validateData('title', $ln->gd("cp.form.fld.companyName.err"));
            $validate->validateData('reg_number', $ln->gd("cp.form.fld.regNumber.err"));
            $validate->validateData('agree_condition' , $ln->gd('cp.form.fld.termsAndCondition.err'));

            $trainees = $fn->getPostParam('trainees', array());
            $traineeData = array();

            foreach($trainees as $rndNo){
                $arr['rndNo']     = $rndNo;
                $arr['email']     = $fn->getPostParam($rndNo . '__id_card_no');
                $arr['course_id'] = $fn->getPostParam($rndNo . '__course_id');

                $traineeData[] = $arr;
            }
            
            for($i = 0; $i < count($traineeData); $i++){
                $email = $traineeData[$i]['email'];
                $course_id = $traineeData[$i]['course_id'];
                for($j = 0; $j < count($traineeData); $j++){
                    $email2 = $traineeData[$j]['email'];
                    $course_id2 = $traineeData[$j]['course_id'];
                    $rndNo = $traineeData[$j]['rndNo'];
                    
                    if ($i != $j && $email == $email2 && $course_id == $course_id2){
                        $courseFld = $rndNo . '__course_id';
                        $validate->errorArray[$courseFld]['name'] = $courseFld;
                        $validate->errorArray[$courseFld]['msg']  = $ln->gd('cp.form.fld.duplicateCourse.err');
                    }
                }
            }
            
            foreach($trainees as $rndNo){
                $validate->validateData($rndNo . '__first_name', $ln->gd("cp.form.fld.traineeFirstName.err"));
                $validate->validateData($rndNo . '__last_name', $ln->gd("cp.form.fld.traineeLastName.err"));
                $validate->validateData($rndNo . '__course_id', $ln->gd('cp.form.fld.course.err'));
                //$validate->validateData($rndNo . '__email', $ln->gd("cp.form.fld.traineeEmail.err"));
                $validate->validateData($rndNo . '__id_card_no', $ln->gd("cp.form.fld.traineeIdNo.err"));
            }

        } else {
            $validate->validateData('first_name', $ln->gd('cp.form.fld.firstName.err'));
            $validate->validateData('last_name' , $ln->gd('cp.form.fld.lastName.err'));
            $validate->validateData('course_id', $ln->gd('cp.form.fld.course.err'));
            $validate->validateData('agree_condition' , $ln->gd('cp.form.fld.termsAndCondition.err'));
        }
        $validate->validateData('email', $ln->gd('cp.form.fld.email.err'), 'email');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');
        
        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'payment_method');
        $fa = $fn->addToFieldsArray($fa, 'organization_id');
        $fa = $fn->addToFieldsArray($fa, 'modName');
        $fa['contactModName'] = $fn->getPostParam('w-ecommerce-shippingDetails_contactModName');
        
        if ($memberType == 'pms_company'){
            $fa = $fn->addToFieldsArray($fa, 'title');
            $fa = $fn->addToFieldsArray($fa, 'reg_number');
            $fa = $fn->addToFieldsArray($fa, 'contact_name');
            $fa = $fn->addToFieldsArray($fa, 'contact_email');
            $fa = $fn->addToFieldsArray($fa, 'contact_position');
            $fa = $fn->addToFieldsArray($fa, 'contact_phone');
            $fa = $fn->addToFieldsArray($fa, 'nature_of_business');
            $fa = $fn->addToFieldsArray($fa, 'address1');
            $fa = $fn->addToFieldsArray($fa, 'address2');
            $fa = $fn->addToFieldsArray($fa, 'address_country_code');
            $fa = $fn->addToFieldsArray($fa, 'fax');

            $trainees = $fn->getPostParam('trainees', array());

            $traineeData = array();

            foreach($trainees as $rndNo){
                $arr['existing_contact_id'] = $fn->getPostParam($rndNo . '__existing_contact_id');
                $arr['first_name']     = $fn->getPostParam($rndNo . '__first_name');
                $arr['last_name']      = $fn->getPostParam($rndNo . '__last_name');
                $arr['gender']         = $fn->getPostParam($rndNo . '__gender');
                $arr['marital_status'] = $fn->getPostParam($rndNo . '__marital_status');
                $arr['id_card_no']     = $fn->getPostParam($rndNo . '__id_card_no');
                $arr['date_of_birth']  = $fn->getPostParam($rndNo . '__date_of_birth');
                $arr['nationality']    = $fn->getPostParam($rndNo . '__nationality');
                $arr['race']           = $fn->getPostParam($rndNo . '__race');
                $arr['phone']          = $fn->getPostParam($rndNo . '__phone');
                $arr['fax']            = $fn->getPostParam($rndNo . '__fax');
                $arr['mobile']         = $fn->getPostParam($rndNo . '__mobile');
                //$arr['email']          = $fn->getPostParam($rndNo . '__email');
                $arr['address1']       = $fn->getPostParam($rndNo . '__address1');
                $arr['address2']       = $fn->getPostParam($rndNo . '__address2');

                $arr['course_id']        = $fn->getPostParam($rndNo . '__course_id');
                $arr['course_language']  = $fn->getPostParam($rndNo . '__course_language');
                $arr['applying_for_sdf'] = $fn->getPostParam($rndNo . '__applying_for_sdf');
                $arr['reference_no']     = $fn->getPostParam($rndNo . '__reference_no');

                $arr['school_highest_qual']  = $fn->getPostParam($rndNo . '__school_highest_qual');
                $arr['position']             = $fn->getPostParam($rndNo . '__position');
                $arr['salary_range']         = $fn->getPostParam($rndNo . '__salary_range');

                $arr['nature_of_business']   = $fn->getPostParam($rndNo . '__nature_of_business');
                $arr['course_training_date'] = $fn->getPostParam($rndNo . '__course_training_date');

                $traineeData[] = $arr;
            }

            $fa['traineeData'] = $traineeData;

        } else {
            $fa = $fn->addToFieldsArray($fa, 'course_id');
            $fa = $fn->addToFieldsArray($fa, 'course_language');
            $fa = $fn->addToFieldsArray($fa, 'course_training_date');
            $fa = $fn->addToFieldsArray($fa, 'reference_no');
            $fa = $fn->addToFieldsArray($fa, 'applying_for_sdf');

            $fa = $fn->addToFieldsArray($fa, 'gender');
            $fa = $fn->addToFieldsArray($fa, 'marital_status');
            $fa = $fn->addToFieldsArray($fa, 'id_card_no');
            $fa = $fn->addToFieldsArray($fa, 'nationality');
            $fa = $fn->addToFieldsArray($fa, 'race');
            $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
            $fa = $fn->addToFieldsArray($fa, 'emergency_contact_name');
            $fa = $fn->addToFieldsArray($fa, 'nature_of_business');
            $fa = $fn->addToFieldsArray($fa, 'emergency_contact_mobile');
            $fa = $fn->addToFieldsArray($fa, 'emergency_contact_office_no');
            $fa = $fn->addToFieldsArray($fa, 'school_name');
            $fa = $fn->addToFieldsArray($fa, 'school_country');
            $fa = $fn->addToFieldsArray($fa, 'school_from');
            $fa = $fn->addToFieldsArray($fa, 'school_to');
            $fa = $fn->addToFieldsArray($fa, 'school_highest_qual');
            $fa = $fn->addToFieldsArray($fa, 'company_name');
            $fa = $fn->addToFieldsArray($fa, 'position');
            $fa = $fn->addToFieldsArray($fa, 'company_phone');
            $fa = $fn->addToFieldsArray($fa, 'company_roc_no');
            $fa = $fn->addToFieldsArray($fa, 'company_address');
            $fa = $fn->addToFieldsArray($fa, 'company_po_code');
            $fa = $fn->addToFieldsArray($fa, 'company_fax');
            $fa = $fn->addToFieldsArray($fa, 'yr_of_exp');
            $fa = $fn->addToFieldsArray($fa, 'salary_range');
            $fa = $fn->addToFieldsArray($fa, 'apply_for_sdf');
        }
        
        return $fa;
    }

    /**
     *
     */
    function getButtons($contObj) {
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$contObj;
        
        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $histUrl = "/index.php?_theme=quest&_spAction=trainingHistory&showHTML=0";

        $text = "
        <div class='floatbox shopBtns' modName='{$c->modName}'>
            <div class='float_right button btnCancel'>
                <a href='javascript:void(0);'>
                    {$ln->gd('cancel')}
                </a>
            </div>
            <div class='float_right button btnProceedToConfirm1'>
                <a href='javascript:void(0);'>
                    {$ln->gd($c->saveContinue)}
                </a>
            </div>
            <div class='float_right button'>
                <a href='javascript:void(0);' link='{$histUrl}' class='jqui-dialog'>
                    {$ln->gd('history')}
                </a>
            </div>
            <div class='float_right button btnApply'>
                <a href='javascript:void(0);' btnApplyLink>
                    {$ln->gd('apply')}
                </a>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $_SESSION['shippingDetails'] = $this->getFields();

        return $validate->getSuccessMessageXML($_POST['returnUrl']);
    }
}