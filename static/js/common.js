$(function(){

    $(document).ajaxError(function(event, request, settings){
        var message = '알수없는 오류가 발생하였습니다.';
        if( typeof request.responseJSON != 'undefined' && typeof request.responseJSON.message != 'undefined' ) {
            message = request.responseJSON.message;
        }
        else {
            if( request.status == 500 ) message = '서버 코드 오류가 발생하였습니다.\n관리자에게 문의하세요';
            else if ( request.status == 401 ) message = '해당 명령을 실행할 권한이 없습니다.';
        }
        toastr.error(message, '오류 발생');
    }).ajaxStart(function(){
        $.blockUI();
    }).ajaxComplete(function(){
        $.unblockUI();
    });

});

/***********************************************************************************
 * 사용자 로그인
 ***********************************************************************************/
$(function(){
    $("form[data-role='form-login']").ajaxForm({
        url : base_url + '/api/members/login',
        type : 'POST',
        success:function(res){
            if(res.result == true) {
                location.href = res.reurl ? res.reurl : base_url;
            }
        },
        error:function(e){
            $('input[name="login_pass"]').val('');
        }
    });
});

/***********************************************************************************
 * 사용자 회원가입
 ***********************************************************************************/
$(function(){
    $("form[data-role='form-join']").on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type : 'PUT',
            data : $(this).serialize(),
            url : base_url + '/api/members/info',
            success:function(res){
                if(res.result == true) {
                    location.href = base_url + "/members/welcome";
                }
            }
        });
    });
});