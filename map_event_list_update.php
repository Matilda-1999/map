<?php
$sub_menu = '710100';
include_once('./_common.php');

check_demo();
auth_check($auth[$sub_menu], 'd');
check_token();

$ma_id = isset($_POST['ma_id']) ? (int)$_POST['ma_id'] : 0;

if (!isset($_POST['chk']) || !is_array($_POST['chk']) || !count($_POST['chk'])) {
	alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}


$count = count($_POST['chk']);
if ($_POST['act_button'] == "선택 수정") {
	for ($i=0; $i<$count; $i++) {
		// 실제 번호를 넘김
		$k = $_POST['chk'][$i];
		
		$sql_common = "
			me_title		= '{$_POST['me_title'][$k]}',
			me_keyword		= '{$_POST['me_keyword'][$k]}',
			me_per_s		= '{$_POST['me_per_s'][$k]}',
			me_per_e		= '{$_POST['me_per_e'][$k]}',
			me_replay_cnt	= '{$_POST['me_replay_cnt'][$k]}',
			me_now_cnt		= '{$_POST['me_now_cnt'][$k]}',
			me_use			= '{$_POST['me_use'][$k]}'
		";
		$sql = " update {$g5['map_event_table']} set {$sql_common} where me_id = '{$_POST['me_id'][$k]}'";
		sql_query($sql);
	}
} else if ($_POST['act_button'] == "선택 삭제") {
	for ($i=0; $i<$count; $i++) {
		// 실제 번호를 넘김
		$k = $_POST['chk'][$i];
		$sql = " delete from {$g5['map_event_table']} where me_id = '{$_POST['me_id'][$k]}'";
		sql_query($sql);
	}
}

goto_url('./map_event_list.php?ma_id='.$ma_id.'&'.$qstr);
?>