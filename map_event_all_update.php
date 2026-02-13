<?php
$sub_menu = "710500";
include_once('./_common.php');

check_demo();
auth_check($auth[$sub_menu], 'w');
check_token();

if ($_POST['act_button'] == "신규 추가") {
    $ma_id          = (int)$_POST['ma_id'];
    $me_title       = sql_real_escape_string($_POST['me_title']);
    $me_content     = sql_real_escape_string($_POST['me_content']);
    $me_per_s       = (int)$_POST['me_per_s'];
    $me_per_e       = (int)$_POST['me_per_e'];
    $me_replay_cnt  = (int)$_POST['me_replay_cnt'];
    $me_keyword     = sql_real_escape_string($_POST['me_keyword']);
    $me_get_item    = (int)$_POST['me_get_item'];
    $me_get_money   = (int)$_POST['me_get_money'];

    if (!$ma_id || !$me_title) {
        alert("지역과 제목은 필수 입력 항목입니다.");
    }

    $sql = " INSERT INTO {$g5['map_event_table']}
                SET ma_id = '{$ma_id}',
                    me_title = '{$me_title}',
                    me_content = '{$me_content}',
                    me_per_s = '{$me_per_s}',
                    me_per_e = '{$me_per_e}',
                    me_replay_cnt = '{$me_replay_cnt}',
                    me_keyword = '{$me_keyword}',
                    me_get_item = '{$me_get_item}',
                    me_get_money = '{$me_get_money}',
                    me_use = '1' ";
    sql_query($sql);
    
    alert("새 이벤트가 등록되었습니다.");
    goto_url('./map_event_all_manage.php');
    exit;
}

if (!isset($_POST['chk']) || !is_array($_POST['chk']) || !count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if ($_POST['act_button'] == "선택 수정") {
    foreach ($_POST['chk'] as $me_id) {
        $me_id = (int)$me_id;
        
        $sql = " UPDATE {$g5['map_event_table']}
                    SET ma_id         = '".(int)$_POST['ma_id'][$me_id]."',
                        me_title      = '".sql_real_escape_string($_POST['me_title'][$me_id])."',
                        me_content    = '".sql_real_escape_string($_POST['me_content'][$me_id])."',
                        me_per_s      = '".(int)$_POST['me_per_s'][$me_id]."',
                        me_per_e      = '".(int)$_POST['me_per_e'][$me_id]."',
                        me_replay_cnt = '".(int)$_POST['me_replay_cnt'][$me_id]."',
                        me_keyword    = '".sql_real_escape_string($_POST['me_keyword'][$me_id])."',
                        me_use        = '".(isset($_POST['me_use'][$me_id]) ? 1 : 0)."'
                  WHERE me_id = '{$me_id}' ";
        sql_query($sql);
    }
    alert("수정되었습니다.");

} else if ($_POST['act_button'] == "선택 삭제") {
    foreach ($_POST['chk'] as $me_id) {
        $me_id = (int)$me_id;
        sql_query(" DELETE FROM {$g5['map_event_table']} WHERE me_id = '{$me_id}' ");
    }
    alert("삭제되었습니다.");
}

goto_url('./map_event_all_manage.php');
?>