<ul class="nav nav-justified nav-pills">
    <li class="active"><a href="<?=base_url("members/info")?>">회원정보 보기</a></li>
    <li><a href="<?=base_url("members/point")?>">포인트 내역</a></li>
    <li><a href="<?=base_url("members/scrap")?>">스크랩 보기</a></li>
    <li><a href="<?=base_url("members/mypost")?>">작성글 보기</a></li>
    <li><a href="<?=base_url("members/message")?>">쪽지함 보기</a></li>
    <li><a href="<?=base_url("members/social")?>">소셜연동 설정</a></li>
</ul>
<article id="myinfo">
    <h1 class="page-title">회원정보</h1>
    <dl class="dl-horizontal">
        <dt>이메일 주소</dt>
        <dd><?=$this->member->info('userid')?></dd>
        <dt>닉네임</dt>
        <dd><?=$this->member->info('nickname')?></dd>
        <dt>회원 포인트</dt>
        <dd><?=$this->member->info('point')?>&nbsp;<a href="<?=base_url("members/porint")?>" class="btn btn-xs btn-default"><i class="fa fa-search"></i>&nbsp;내역보기</a></dd>
        <dt>회원 권한</dt>
        <dd><?=$this->member->auth()?> (<?=$this->member->auth_name()?>)</dd>
        <dt>가입일</dt>
        <dd><?=$this->member->info('regtime')?></dd>
        <dt>최근 로그인</dt>
        <dd><?=$this->member->info('logtime')?></dd>
        <dt>프로필</dt>
        <dd><?=$this->member->info('profile')?></dd>
    </dl>
    <div class="btn-group">
        <a class="btn btn-default" href="<?=base_url("members/modify")?>"><i class="fa fa-pencil"></i>&nbsp;정보수정</a>
        <a class="btn btn-default" href="<?=base_url("members/modify")?>"><i class="fa fa-key"></i>&nbsp;비밀번호 변경</a>
        <a class="btn btn-default" href="<?=base_url("members/modify")?>"><i class="fa fa-remove"></i>&nbsp;회원탈퇴</a>
    </div>
</article>
<style>
.dl-horizontal > dt,
.dl-horizontal > dd { margin-bottom:10px; }
</style>