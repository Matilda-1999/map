<?php
$sub_menu = "710200";
include_once('./_common.php');

check_demo();
auth_check($auth[$sub_menu], 'w');

$ma_id = isset($_POST['ma_id']) ? (int)$_POST['ma_id'] : 0;
if (!$ma_id) {
    alert('출발 지역 정보가 올바르지 않습니다.');
}

// 기존 규칙 삭제
sql_query("DELETE FROM {$g5['map_move_rules_table']} WHERE mr_from_ma_id = '{$ma_id}'");

// 새로운 규칙 추가
if (isset($_POST['dest_maps']) && is_array($_POST['dest_maps'])) {
    foreach ($_POST['dest_maps'] as $dest_ma_id) {
        $dest_ma_id = (int)$dest_ma_id;
        if (!$dest_ma_id) continue;

        $keyword = isset($_POST['keywords'][$dest_ma_id]) ? trim($_POST['keywords'][$dest_ma_id]) : '';

        $sql = "INSERT INTO {$g5['map_move_rules_table']}
                SET mr_from_ma_id = '{$ma_id}',
                    mr_to_ma_id   = '{$dest_ma_id}',
                    mr_keyword    = '{$keyword}'";
        sql_query($sql);
    }
}

goto_url('./map_move_list.php?ma_id='.$ma_id, false);
?>