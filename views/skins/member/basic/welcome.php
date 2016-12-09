<?php
/*************************************************
 * 사용자 회원가입 완료 페이지
 *************************************************/
?>
<article id="member-join-done">
    <header>
        <h1 class="page-title">회원가입을 환영합니다.</h1>
    </header>
    <p class="text-center" style="padding:50px 20px">
        <span class="text-primary"><?=$this->member->info('nickname')?></span>님 회원가입을 진심으로 환영합니다.
    </p>
    <div class="text-center">
        <a href="<?=base_url()?>" class="btn btn-primary"><i class="fa fa-home"></i>&nbsp;메인 페이지로</a>
    </div>
</article>
