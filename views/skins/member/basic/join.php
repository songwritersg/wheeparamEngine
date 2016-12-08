<article id="join-form">
    <?=$form_open?>
    <header>
        <h1 class="page-title"><i class="fa fa-users"></i>&nbsp;회원가입</h1>
    </header>

    <fieldset>
        <div class="form-group">
            <h3 class="group-title">사이트 이용약관</h3>
            <div class="agreement"><?=nl2br($this->site->config('agreement_site'))?></div>
            <div class="checkbox">
                <label><input type="checkbox" name="agreement[]">&nbsp;사이트 이용약관에 동의합니다.</label>
            </div>
            <h3 class="group-title">개인정보취급방침</h3>
            <div class="agreement"><?=nl2br($this->site->config('agreement_privacy'))?></div>
            <div class="checkbox">
                <label><input type="checkbox" name="agreement[]">&nbsp;개인정보 취급방침에 동의합니다.</label>
            </div>
            <div class="checkbox">
                <label><input type="checkbox" id="agree_all" name="agree" value="Y">&nbsp;모두 동의합니다.</label>
            </div>
        </div>
    </fieldset>
    <hr>
    <fieldset>
        <div class="form-group">
            <label class="control-label col-sm-2" for="userid">아이디</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="userid" name="userid" required>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-2" for="userpass">비밀번호</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="userpass" name="userpass" required maxlength="20">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-2" for="userpass_confirm">비밀번호</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="userpass_confirm" name="userpass_confirm" required maxlength="20">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-2" for="usernick">닉네임</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="usernick" name="usernick" required maxlength="20">
            </div>
        </div>

        <?php if( $use_message ) :?>
        <div class="form-group">
            <label class="control-label col-sm-2">쪽지 설정</label>
            <div class="col-sm-10">
                <div class="radio-inline"><label><input type="radio" value="A" name="use_message" checked="checked">&nbsp;전체 수신</label></div>
                <div class="radio-inline"><label><input type="radio" value="F" name="use_message">&nbsp;친구만 수신</label></div>
                <div class="radio-inline"><label><input type="radio" value="N" name="use_message">&nbsp;전체 거부</label></div>
            </div>
        </div>
        <?php endif;?>

        <?php if( $use_profile ) :?>
        <div class="form-group">
            <label class="control-label col-sm-2">프로필 공개</label>
            <div class="col-sm-10">
                <div class="radio-inline"><label><input type="radio" value="A" name="use_profile" checked="checked">&nbsp;전체 공개</label></div>
                <div class="radio-inline"><label><input type="radio" value="F" name="use_profile">&nbsp;친구만 공개</label></div>
                <div class="radio-inline"><label><input type="radio" value="N" name="use_profile">&nbsp;전체 비공개</label></div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2">프로필 설정</label>
            <div class="col-sm-10">
            </div>
        </div>
        <?php endif;?>
    </fieldset>

    <div class="form-group">
        <div class="text-center">
            <button type="submit" class="btn btn-lg btn-primary"><i class="fa fa-check"></i>&nbsp;회원가입</button>
        </div>
    </div>
    <?=$form_close?>
</article>
<script>
    $(function(){
        $(".agreement").slimscroll({height:'100px'});

        $("#agree_all").on('change.agree_all_changed' ,function(){
            $("input[type='checkbox'][name='agreement[]']").prop('checked', $(this).prop('checked') );
        });

        $("input[type='checkbox'][name='agreement[]").on('change.agree_changed',function(){
            var checked = 0;
            $("input[type='checkbox'][name='agreement[]").each(function(){
                checked += ($(this).prop('checked')) ? 1 : 0;
            });
            $("#agree_all").prop('checked', (checked == 2) );
        });
    });
</script>
