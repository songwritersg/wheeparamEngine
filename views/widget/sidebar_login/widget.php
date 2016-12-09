<div class="box">

    <?php if($this->member->is_login()) :?>

    <header>
        <h4 class="page-title">로그인 정보</h4>
    </header>
    <dl class="user-info">
        <dd>닉네임</dd>
        <dt><?=$this->member->info('nickname')?></dt>
        <dd>레벨</dd>
        <dt><?=$this->member->auth()?> (<?=$this->member->auth_name()?>)</dt>
        <?php if($this->site->config('point_use')) :?>
            <dd><?=$this->site->config('point_name')?></dd>
            <dt><?=number_format($this->member->info('point'))?>&nbsp;<a href="<?=base_url("members/info/point")?>" class="btn btn-mini btn-default"><i class="fa fa-search"></i></a></dt>
        <?php endif;?>
    </dl>

    <a href="<?=base_url("members/info")?>" class="btn btn-default btn-sm btn-block"><i class="fa fa-user"></i>&nbsp;내 정보</a>
    <?php if($this->site->config('message_use') == 'Y') :?>
    <a href="<?=base_url("members/info/message")?>" class="btn btn-default btn-sm btn-block"><i class="fa fa-envelope-o"></i>&nbsp;쪽지함</a>
    <?php endif;?>
    <a href="<?=base_url("members/logout")?>" class="btn btn-default btn-sm btn-block" onclick="return confirm('로그아웃 하시겠습니까?');"><i class="fa fa-sign-out"></i>&nbsp;로그아웃</a>

    <?php else: ?>

    <header>
        <h4 class="page-title">로그인</h4>
    </header>
    <form autocomplete="off" data-role="form-login" method="post" accept-charset="utf-8">
        <input type="hidden" name="reurl" value="<?=current_full_url()?>">
        <fieldset>
            <div class="form-group form-group-sm">
                <input type="text" class="form-control" name="login_id" placeholder="아이디">
                <input type="password" class="form-control" name="login_pass" placeholder="비밀번호" style="margin-top:10px;">
            </div>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fa fa-check"></i>&nbsp;로그인</button>
        <a href="<?=base_url("members/join")?>" class="btn btn-default btn-block btn-sm"><i class="fa fa-user-plus"></i>&nbsp;회원 가입</a>

    </form>
    <?php endif;?>
</div>