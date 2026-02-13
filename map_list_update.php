<?php
$sub_menu = '710100';
include_once('./_common.php');

$map_img_dir = G5_DATA_PATH.'/map_img';
@mkdir($map_img_dir, G5_DIR_PERMISSION);
@chmod($map_img_dir, G5_DIR_PERMISSION);

check_demo();
auth_check($auth[$sub_menu], 'd');
check_token();

if (!isset($_POST['chk']) || !is_array($_POST['chk']) || !count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if ($_POST['act_button'] == "선택 수정") {
    foreach ($_POST['chk'] as $ma_id) {
        $ma_id = (int)$ma_id;
        if (!$ma_id) continue;

        // 안전한 파일 처리 로직
        $ma_img = isset($_POST['ma_img'][$ma_id]) ? $_POST['ma_img'][$ma_id] : '';

        // 1. 체크박스로 이미지 삭제를 명시했을 경우
        if (isset($_POST['ma_img_del'][$ma_id]) && $_POST['ma_img_del'][$ma_id]) {
            if ($ma_img) {
                $old_file_path = str_replace(G5_URL, G5_PATH, $ma_img);
                if(is_file($old_file_path)) {
                    @unlink($old_file_path);
                }
            }
            $ma_img = '';
        }

        // 2. 새로운 파일이 업로드되었을 경우
        if (isset($_FILES['ma_img_file']['name'][$ma_id]) && $_FILES['ma_img_file']['name'][$ma_id]) {
            $dest_path = $map_img_dir.'/map_'.$ma_id.'_'.time().'.'.pathinfo($_FILES['ma_img_file']['name'][$ma_id], PATHINFO_EXTENSION);

            if (move_uploaded_file($_FILES['ma_img_file']['tmp_name'][$ma_id], $dest_path)) {
                // 새 파일 저장 성공 시, 이전 이미지 삭제
                if ($ma_img) {
                    $old_file_path = str_replace(G5_URL, G5_PATH, $ma_img);
                    if(is_file($old_file_path)) {
                        @unlink($old_file_path);
                    }
                }
                // DB에 저장할 변수 값을 새 이미지 URL로 교체하고, 이미지 사이즈 자동 입력
                $ma_img = str_replace(G5_PATH, G5_URL, $dest_path);
                $size = getimagesize($dest_path);
                if ($size) {
                    $_POST['ma_width'][$ma_id] = $size[0];
                    $_POST['ma_height'][$ma_id] = $size[1];
                }
            }
        }

        // 안전한 텍스트 처리 로직
        $ma_name_safe = sql_real_escape_string($_POST['ma_name'][$ma_id]);
        $ma_content_safe = sql_real_escape_string($_POST['ma_content'][$ma_id]);
        
        // 기타 변수 할당
        $ma_use         = isset($_POST['ma_use'][$ma_id]) ? 1 : 0;
        $ma_start       = isset($_POST['ma_start'][$ma_id]) ? 1 : 0;
        $ma_use_dungeon = isset($_POST['ma_use_dungeon'][$ma_id]) ? 1 : 0;
        $ma_event_type  = isset($_POST['ma_event_type'][$ma_id]) ? $_POST['ma_event_type'][$ma_id] : 'random';

        // 최종 데이터베이스 업데이트
        $sql = "UPDATE {$g5['map_table']}
            SET ma_name           = '{$ma_name_safe}',
                ma_event_type     = '{$ma_event_type}',
                ma_parent         = '{$_POST['ma_parent'][$ma_id]}',
                ma_use            = '{$ma_use}',
                ma_start          = '{$ma_start}',
                ma_use_dungeon    = '{$ma_use_dungeon}',
                ma_top            = '{$_POST['ma_top'][$ma_id]}',
                ma_left           = '{$_POST['ma_left'][$ma_id]}',
                ma_width          = '{$_POST['ma_width'][$ma_id]}',
                ma_height         = '{$_POST['ma_height'][$ma_id]}',
                ma_img            = '{$ma_img}',
                ma_content        = '{$ma_content_safe}'
            WHERE ma_id = '{$ma_id}'";
        sql_query($sql);
    }
} else if ($_POST['act_button'] == "선택 삭제") {
    foreach ($_POST['chk'] as $ma_id) {
        $ma_id = (int)$ma_id;
        if (!$ma_id) continue;

        $sub_maps = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['map_table']} WHERE ma_parent = '{$ma_id}' AND ma_id != '{$ma_id}'");
        if($sub_maps['cnt'] > 0) {
            $map_info = sql_fetch("SELECT ma_name FROM {$g5['map_table']} WHERE ma_id = '{$ma_id}'");
            alert("{$map_info['ma_name']}(id:{$ma_id}) 지역은 하위 지역을 가지고 있어 삭제할 수 없습니다. 하위 지역을 먼저 삭제하거나 다른 지역으로 이동시켜 주세요.");
        }
        
        $row = sql_fetch("SELECT ma_img FROM {$g5['map_table']} WHERE ma_id = '{$ma_id}'");
        if ($row['ma_img']) {
            $img_path = str_replace(G5_URL, G5_PATH, $row['ma_img']);
            if(is_file($img_path)) {
                @unlink($img_path);
            }
        }

        sql_query("DELETE FROM {$g5['map_table']} WHERE ma_id = '{$ma_id}'");
        sql_query("DELETE FROM {$g5['map_event_table']} WHERE ma_id = '{$ma_id}'");
        sql_query("UPDATE {$g5['character_table']} SET ma_id = 0 WHERE ma_id = '{$ma_id}'");
    }
}

else if ($_POST['act_button'] == "목적지 키워드 일괄 변경") {
    $dest_keyword = sql_real_escape_string(trim($_POST['dest_batch_keyword']));
    
    if(!$dest_keyword) {
        alert("적용할 키워드를 입력해 주세요.");
    }

    foreach ($_POST['chk'] as $ma_id) {
        $ma_id = (int)$ma_id;
        if (!$ma_id) continue;

        // 선택한 지역(ma_id)이 '목적지'인 모든 규칙의 키워드를 한 번에 변경합니다.
        sql_query("UPDATE {$g5['map_move_rules_table']} 
                   SET mr_keyword = '{$dest_keyword}' 
                   WHERE mr_to_ma_id = '{$ma_id}'");
    }
    alert("선택하신 지역들로 향하는 모든 통행 키워드가 변경되었습니다.");
}

else if ($_POST['act_button'] == "선택 소속 일괄 변경") {
    $move_parent_id = (int)$_POST['move_parent_id'];
    
    if(!$move_parent_id) {
        alert("이동할 상위 지역을 선택해 주세요.");
    }

    foreach ($_POST['chk'] as $ma_id) {
        $ma_id = (int)$ma_id;
        if (!$ma_id) continue;

        // 선택한 지역들의 부모를 고른 지역으로 한꺼번에 변경
        sql_query("UPDATE {$g5['map_table']} 
                   SET ma_parent = '{$move_parent_id}' 
                   WHERE ma_id = '{$ma_id}'");
    }
    alert("선택하신 지역들의 소속이 변경되었습니다.");
}

goto_url('./map_list.php?'.$qstr);
?>