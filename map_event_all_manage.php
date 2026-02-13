<?php
$sub_menu = "710500";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '이벤트 통합 관리';
include_once('./admin.head.php');

// 지역별 필터링 (ma_id)
$sca = isset($_REQUEST['sca']) ? (int)$_REQUEST['sca'] : 0;
$sql_search = "";
if ($sca) {
    $sql_search = " WHERE e.ma_id = '{$sca}' ";
}

// 모든 지역 정보 가져오기 (필터 및 선택용)
$maps = array();
$map_result = sql_query("select ma_id, ma_name from {$g5['map_table']} order by ma_id asc");
while($m = sql_fetch_array($map_result)) { $maps[] = $m; }

// 모든 이벤트 목록 가져오기
$sql_common = " from {$g5['map_event_table']} e left join {$g5['map_table']} m on (e.ma_id = m.ma_id) ";
$sql_order = " order by e.ma_id asc, e.me_id desc ";

$sql = " select e.*, m.ma_name {$sql_common} {$sql_search} {$sql_order} ";
$result = sql_query($sql);
$total_count = sql_num_rows($result);

$token = get_token();
?>

<div class="local_ov01 local_ov">
    <div style="float:left;">
        전체 <?php echo number_format($total_count); ?>건
    </div>
    <div style="float:right;">
        <form name="fcategory" id="fcategory" method="get" style="display:inline-block;">
            <select name="sca" onchange="location.href='<?php echo $_SERVER['PHP_SELF']; ?>?sca='+this.value;">
                <option value="">전체 지역 보기</option>
                <?php foreach($maps as $m) { ?>
                    <option value="<?php echo $m['ma_id'] ?>" <?php echo ($sca == $m['ma_id']) ? 'selected' : ''; ?>><?php echo $m['ma_name'] ?></option>
                <?php } ?>
            </select>
        </form>
    </div>
    <div style="clear:both;"></div>
</div>

<div class="form-area" style="margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; background: #f9f9f9;">
    <h2 class="h2_frm">새 이벤트 빠른 추가</h2>
    <form name="feventadd" method="post" action="./map_event_all_update.php" autocomplete="off">
        <input type="hidden" name="token" value="<?php echo $token ?>">
        <input type="hidden" name="act_button" value="신규 추가">
        
        <div class="tbl_frm01 tbl_wrap">
            <table>
                <colgroup>
                    <col style="width:120px;">
                    <col>
                    <col style="width:120px;">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th scope="row">적용 지역</th>
                    <td>
                        <select name="ma_id" required>
                            <option value="">지역 선택</option>
                            <?php foreach($maps as $m) { ?>
                                <option value="<?php echo $m['ma_id'] ?>"><?php echo $m['ma_name'] ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <th scope="row">이벤트 제목</th>
                    <td><input type="text" name="me_title" required class="frm_input" size="30"></td>
                </tr>
                <tr>
                    <th scope="row">확률 (0~100)</th>
                    <td>
                        <input type="text" name="me_per_s" class="frm_input" size="5" placeholder="시작"> ~
                        <input type="text" name="me_per_e" class="frm_input" size="5" placeholder="끝">
                    </td>
                    <th scope="row">최대 획득 횟수</th>
                    <td>
                        <input type="text" name="me_replay_cnt" class="frm_input" size="10" value="0">
                        <span class="frm_info">0 입력 시 무제한</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">해금 키워드</th>
                    <td><input type="text" name="me_keyword" class="frm_input" size="30" placeholder="키워드|구분"></td>
                    <th scope="row">보상 (템ID/돈)</th>
                    <td>
                        <input type="text" name="me_get_item" class="frm_input" size="5" placeholder="아이템ID"> / 
                        <input type="text" name="me_get_money" class="frm_input" size="10" placeholder="화폐량">
                    </td>
                </tr>
                <tr>
                    <th scope="row">이벤트 내용</th>
                    <td colspan="3">
                        <textarea name="me_content" class="frm_input" style="width:100%; height:60px;"></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="btn_confirm01 btn_confirm">
            <input type="submit" value="이벤트 등록" class="btn_submit">
        </div>
    </form>
</div>

<form name="feventall" id="feventall" method="post" action="./map_event_all_update.php" onsubmit="return feventall_submit(this);">
<input type="hidden" name="token" value="<?php echo $token ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <thead>
    <tr>
        <th scope="col"><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
        <th scope="col">적용 지역</th>
        <th scope="col">이벤트 제목 / 내용</th>
        <th scope="col">확률 / 횟수</th>
        <th scope="col">해금 키워드</th>
        <th scope="col">사용</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_chk"><input type="checkbox" name="chk[]" value="<?php echo $row['me_id'] ?>"></td>
        <td class="td_mng">
            <select name="ma_id[<?php echo $row['me_id'] ?>]">
                <?php foreach($maps as $m) { ?>
                    <option value="<?php echo $m['ma_id'] ?>" <?php echo ($m['ma_id'] == $row['ma_id']) ? 'selected' : ''; ?>><?php echo $m['ma_name'] ?></option>
                <?php } ?>
            </select>
        </td>
        <td class="td_left">
            <input type="text" name="me_title[<?php echo $row['me_id'] ?>]" value="<?php echo get_text($row['me_title']) ?>" class="frm_input" style="width:100%; margin-bottom:5px;">
            <textarea name="me_content[<?php echo $row['me_id'] ?>]" class="frm_input" style="width:100%; height:50px; font-size:11px;"><?php echo $row['me_content'] ?></textarea>
        </td>
        <td class="td_num">
            P: <input type="text" name="me_per_s[<?php echo $row['me_id'] ?>]" value="<?php echo $row['me_per_s'] ?>" class="frm_input" size="2"> ~ <input type="text" name="me_per_e[<?php echo $row['me_id'] ?>]" value="<?php echo $row['me_per_e'] ?>" class="frm_input" size="2"><br>
            C: <input type="text" name="me_replay_cnt[<?php echo $row['me_id'] ?>]" value="<?php echo $row['me_replay_cnt'] ?>" class="frm_input" size="5">
        </td>
        <td><input type="text" name="me_keyword[<?php echo $row['me_id'] ?>]" value="<?php echo get_text($row['me_keyword']) ?>" class="frm_input" style="width:100%;"></td>
        <td class="td_chk"><input type="checkbox" name="me_use[<?php echo $row['me_id'] ?>]" value="1" <?php echo $row['me_use'] ? 'checked' : ''; ?>></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<div class="btn_list01 btn_list">
    <input type="submit" name="act_button" value="선택 수정" onclick="document.pressed=this.value">
    <input type="submit" name="act_button" value="선택 삭제" onclick="document.pressed=this.value">
</div>
</form>

<script>
function feventall_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }
    if(document.pressed == "선택 삭제") {
        if(!confirm("선택한 이벤트를 정말 삭제하시겠습니까?")) return false;
    }
    return true;
}
</script>

<?php include_once('./admin.tail.php'); ?>