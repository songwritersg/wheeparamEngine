<article id="login-form">
    <?=$form_open?>
    <div class="form-group">
        <input type="text" class="form-control" name="login_id" placeholder="아이디" value="<?=set_value('login_id')?>" maxlength="100">
    </div>
    <div class="form-group">
        <input type="password" class="form-control" name="login_pass" placeholder="비밀번호" value="" maxlength="20">
    </div>
    <div class="form-group">
        <div class="pull-left">
            <div class="checkbox" data-toggle="tooltip" title="ㄴㅁㅇㄴㅇ">
                <label><input type="checkbox" name="login_keep" value="Y">&nbsp;로그인 유지</label>
            </div>
        </div>
        <div class="pull-right">
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i>&nbsp;로그인</button>
        </div>
        <div class="clearfix"></div>
    </div>
    <?=$form_close?>
</article>