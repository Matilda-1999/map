<?php
$sub_menu = "710300";
include_once('./_common.php');
$ma_id = isset($_POST['ma_id']) ? (int)$_POST['ma_id'] : 0;

check_demo();
auth_check($auth[$sub_menu], 'w');

if ($_POST['act_button'] == "《 지역 이동") {
    if (isset($_POST['ch_insert']) && is_array($_POST['ch_insert'])) {
        foreach ($_POST['ch_insert'] as $ch_id) {
            $ch_id = (int)$ch_id;
            $sql = " update {$g5['character_table']}
                     set ma_id = '{$ma_id}' 
                     where ch_id = '{$ch_id}'";
            sql_query($sql);
        }
    }
}

if ($_POST['act_button'] == "지역 이탈 》") {
    if (isset($_POST['ch_expert']) && is_array($_POST['ch_expert'])) {
        foreach ($_POST['ch_expert'] as $ch_id) {
            $ch_id = (int)$ch_id;
            $sql = " update {$g5['character_table']}
                     set ma_id = '0' 
                     where ch_id = '{$ch_id}'";
            sql_query($sql);
        }
    }
}

goto_url('./map_member_list.php?ma_id='.$ma_id);
?>