$(function(){

    $("form[data-role='form-join']").on('submit', function(e){
        var $form = $(this);
        e.preventDefault();

        var agreement_check = $form.find('input[name="agree"]:checked');
        if( typeof agreement_check.val() == 'undefined' ||  agreement_check.val() != 'Y' )
        {
            alert('사이트 이용약관과 개인정보 취급방침에 동의하셔야 합니다.');
            agreement_check.focus();
            return;
        }
    });
});
