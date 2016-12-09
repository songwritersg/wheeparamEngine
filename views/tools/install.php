<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/css/install.min.css" rel="stylesheet">
</head>
<body>

<div class="container" id="install">
    <div class="row">

    </div>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">설치 가능 여부 확인</h4>
            </div>
            <table class="table table-striped table-bordered">
                <colgroup>
                    <col class="col-xs-6" />
                    <col class="col-xs-6" />
                </colgroup>
                <tr>
                    <th class="text-center">PHP 5.3 이상</th>
                    <td>
                        <?php if($php_version) : ?><label class="text-success"><i class="glyphicon glyphicon-ok"></i></label>
                        <?php else:?><label class="text-danger"><i class="glyphicon glyphicon-remove"></i></label>
                        <?php endif;?>
                    </td>
                </tr>
                <tr>
                    <th class="text-center">GD</th>
                    <td>
                        <?php if($gd_support) : ?><label class="text-success"><i class="glyphicon glyphicon-ok"></i></label>
                        <?php else:?><label class="text-danger"><i class="glyphicon glyphicon-remove"></i></label>
                        <?php endif;?>
                    </td>
                </tr>
                <tr>
                    <th class="text-center">CURL</th>
                    <td>
                        <?php if($curl_support) : ?><label class="text-success"><i class="glyphicon glyphicon-ok"></i></label>
                        <?php else:?><label class="text-danger"><i class="glyphicon glyphicon-remove"></i></label>
                        <?php endif;?>
                    </td>
                </tr>
                <tr>
                    <th class="text-center">mbstring</th>
                    <td>
                        <?php if($mbstring_support) : ?><label class="text-success"><i class="glyphicon glyphicon-ok"></i></label>
                        <?php else:?><label class="text-danger"><i class="glyphicon glyphicon-remove"></i></label>
                        <?php endif;?>
                    </td>
                </tr>
                <tr>
                    <th class="text-center">JSON</th>
                    <td>
                        <?php if($json_support) : ?><label class="text-success"><i class="glyphicon glyphicon-ok"></i></label>
                        <?php else:?><label class="text-danger"><i class="glyphicon glyphicon-remove"></i></label>
                        <?php endif;?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
    <?php if(! $library_check) :?>
        <p class="alert alert-danger">설치가 불가능합니다.</p>
    <?php else :?>
        <?=form_open(NULL, array("class"=>"form-horizontal"))?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">설치 정보 입력</h4>
            </div>
            <div class="panel-body">
                <?=validation_errors('<p class="alert alert-danger">');?>
                <div class="form-group">
                    <label class="control-label col-sm-4">사이트 이름</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="site_title" value="<?=set_value('site_title','휘파람')?>" required>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label class="control-label col-sm-4">데이타베이스 호스트</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="db_host" value="<?=set_value('db_host','localhost')?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-4">데이타베이스 사용자</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="db_user" value="<?=set_value('db_user','')?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-4">데이타베이스 비밀번호</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="db_pass" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-4">데이타베이스 이름</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="db_name" value="<?=set_value('db_name','')?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-4">데이타베이스 테이블 접두어</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="dbprefix" value="<?=set_value('dbprefix','we_')?>" required>
                        <p class="help-block">가급적 변경하지 마세요</p>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label class="control-label col-sm-4">관리자 닉네임</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="admin_nick" value="<?=set_value('admin_nick')?>" required maxlength="20">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-sm-4">관리자 아이디</label>
                    <div class="col-sm-8">
                        <input type="email" class="form-control" name="admin_id" value="<?=set_value('admin_id')?>" required maxlength="100">
                        <p class="help-block">아이디는 이메일형식을 사용합니다.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-4">관리자 비밀번호</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="admin_pass" required maxlength="20">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-4">관리자 비밀번호 확인</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="admin_pass_confirm" required maxlength="20">
                    </div>
                </div>
            </div>
            <div class="panel-footer text-center">
                <button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-ok"></i>&nbsp;설치 진행</button>
            </div>
        </div>
        <?=form_close()?>
    <?php endif;?>
    </div>
</div>

</body>
</html>