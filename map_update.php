<?php
$sub_menu = "710100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');
check_token();

// type이 CONFIG일 때만 파일 처리 로직을 실행합니다.
if (isset($_POST['type']) && $_POST['type'] == 'CONFIG') {
    $map_img_dir = G5_DATA_PATH.'/map_img'; 

    @mkdir($map_img_dir, G5_DIR_PERMISSION);
    @chmod($map_img_dir, G5_DIR_PERMISSION);

    $cf_map_all_img = $_POST['cf_map_all_img'];

    if (isset($_POST['cf_map_all_img_del']) && $_POST['cf_map_all_img_del']) {
        $old_file_path = str_replace(G5_URL, G5_PATH, $cf_map_all_img);
        @unlink($old_file_path);
        $cf_map_all_img = '';
    }

    if (isset($_FILES['cf_map_all_img_file']['name']) && $_FILES['cf_map_all_img_file']['name']) {
        // 새 파일의 저장 경로를 먼저 정의합니다.
        $file_ext = pathinfo($_FILES['cf_map_all_img_file']['name'], PATHINFO_EXTENSION);
        $dest_filename = 'all_map_'.time().'.'.$file_ext;
        $dest_path = $map_img_dir.'/'.$dest_filename;

        // 새 파일을 먼저 서버로 이동(저장)합니다.
        if (move_uploaded_file($_FILES['cf_map_all_img_file']['tmp_name'], $dest_path)) {
            // 파일 저장이 성공했을 때만 아래 로직을 실행합니다.

            // 기존 이미지가 있었다면 안전하게 삭제합니다.
            $old_file_path = str_replace(G5_URL, G5_PATH, $cf_map_all_img);
            if (is_file($old_file_path)) {
                @unlink($old_file_path);
            }

            // 데이터베이스에 저장할 변수 값을 새 이미지의 URL로 교체합니다.
            $cf_map_all_img = str_replace(G5_PATH, G5_URL, $dest_path);

            // (이전 답변에서 추가된) 이미지 크기 자동 입력 로직
            $size = getimagesize($dest_path);
            if ($size) {
                $_POST['cf_map_all_w'] = $size[0];
                $_POST['cf_map_all_h'] = $size[1];
            }
        }
    }
}


// "신규 지역 추가"일 경우
if(!isset($type) || $type != 'CONFIG') { 
	sql_query ("insert into {$g5['map_table']}
					set ma_parent ='{$_POST['ma_parent']}',
						ma_name ='{$_POST['ma_name']}',
						ma_use = '{$_POST['ma_use']}'
	");
	$ma_id = sql_insert_id(); // 마지막으로 추가된 id를 가져옵니다.

	// 최상위 카테고리로 생성된 경우, 자신의 id를 부모 id로 가집니다.
	if(!$_POST['ma_parent']) { 
		sql_query ("update {$g5['map_table']} set ma_parent = '{$ma_id}' where ma_id = '{$ma_id}'");
	}
    
    // ▼▼▼ 문제가 되었던 쿠키 저장 라인을 삭제했습니다. ▼▼▼
	// set_cookie("co_ma_parent", $_POST['ma_parent'], 30);

} 
// "지역 설정"일 경우
else {
	sql_query ("update {$g5['config_table']}
					set cf_use_map       = '".(isset($_POST['cf_use_map']) ? 1 : 0)."',
						cf_use_map_all   = '".(isset($_POST['cf_use_map_all']) ? 1 : 0)."',
						cf_map_all_img   = '{$cf_map_all_img}',
						cf_map_all_w     = '{$_POST['cf_map_all_w']}',
						cf_map_all_h     = '{$_POST['cf_map_all_h']}',
                        cf_map_all_w     = '{$_POST['cf_map_all_w']}',
						cf_map_all_h     = '{$_POST['cf_map_all_h']}'
	");
}

goto_url('./map_list.php?'.$qstr);
?>