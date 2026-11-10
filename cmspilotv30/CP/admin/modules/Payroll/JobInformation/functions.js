Util.createCPObject('cpm.payroll.jobInformation');

cpm.payroll.jobInformation = {
    init: function(){
        $(".m-payroll_jobInformation #fld_status").livequery('change', function (e){
            var jobInfoStatus = $(this).val();
            if (jobInfoStatus == 'Archive') {
                alert('Please enter TERMINATION INFORMATION of employee if employee is leaving company.');
            }
        });
        
        $(".m-payroll_jobInformation input[name='probationary']").livequery('click', function (e){
            var probation = $(this).val();
            if (probation == 1) {
                $('.row_length_of_probation').removeClass('hideme');
                $('.row_probation_start_date').removeClass('hideme');
                $('.row_probation_end_date').removeClass('hideme');
            } else {
                $('.row_length_of_probation').addClass('hideme');
                $('.row_probation_start_date').addClass('hideme');
                $('.row_probation_end_date').addClass('hideme');
            }
        });

        $(".m-payroll_jobInformation #fld_govt_donation").livequery('change', function (e){
            var donation = $(this).val();

            if (donation == 'CDAC') {
                $('.row_pay_cdac').removeClass('hideme');
                $('.row_pay_sinda').addClass('hideme');
                $('.row_pay_mbmf').addClass('hideme');
                $('.row_pay_eucf').addClass('hideme');
            } else if (donation == 'SINDA') {
                $('.row_pay_cdac').addClass('hideme');
                $('.row_pay_sinda').removeClass('hideme');
                $('.row_pay_mbmf').addClass('hideme');
                $('.row_pay_eucf').addClass('hideme');
            } else if (donation == 'MBMF') {
                $('.row_pay_cdac').addClass('hideme');
                $('.row_pay_sinda').addClass('hideme');
                $('.row_pay_mbmf').removeClass('hideme');
                $('.row_pay_eucf').addClass('hideme');
            } else if (donation == 'EUCF') {
                $('.row_pay_cdac').addClass('hideme');
                $('.row_pay_sinda').addClass('hideme');
                $('.row_pay_mbmf').addClass('hideme');
                $('.row_pay_eucf').removeClass('hideme');
            } else {
                $('.row_pay_cdac').addClass('hideme');
                $('.row_pay_sinda').addClass('hideme');
                $('.row_pay_mbmf').addClass('hideme');
                $('.row_pay_eucf').addClass('hideme');
            }

        });

        $("input[name='employee_name']")
        .livequery(cpm.payroll.jobInformation.employeeName);
    },
    //Auto select patient details
    employeeName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=payroll_jobInformation&_spAction=searchEmployeeDetails&showHTML=0'
            ,minLength : 3
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var employee_id = selectedObj.id
                $('input[name=employee_id]').val(employee_id);
            }
        });
    },

    duplicate: function() {
        if (!confirm("Do you wish to duplicate the Job Information?")){
            return;
        }

        var job_information_id = $('#record_id').val();
        var url = 'index.php?module=payroll_jobInformation&_spAction=duplicateJobInformation&showHTML=0' +
                  '&job_information_id=' + job_information_id;

        $.get(url, {job_information_id: job_information_id}, function (html) {
            alert('Job Information duplicated successfully.');
            var convertUrl = "index.php?_topRm=payroll&module=payroll_jobInformation&_action=edit&record_id=" + html;
            document.location = convertUrl;
        });
    },
}