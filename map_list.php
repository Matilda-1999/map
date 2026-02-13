<?php
$sub_menu = "710100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$token = get_token();

$sql_common = " from {$g5['map_table']} ";
$sql = " select count(*) as cnt {$sql_common} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['PHP_SELF'].'" class="ov_listall">전체 목록</a>';

$g5['title'] = '지역 관리';
include_once ('./admin.head.php');
$colspan = 12;
?>

<div class="form-area">
    <h2 class="h2_frm">지역 설정</h2>
    <form name="fmapconfiglist" method="post" id="fmapconfiglist" action="./map_update.php" autocomplete="off" enctype="multipart/form-data">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="page" value="<?php echo $page ?>">
        <input type="hidden" name="token" value="<?php echo $token ?>">
        <input type="hidden" name="type" value="CONFIG">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <colgroup>
                <col style="width: 120px;">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row"><label for="cf_use_map">지역 기능</label></th>
                <td><input type="checkbox" name="cf_use_map" value="1" id="cf_use_map" <? if($config['cf_use_map']) { ?>checked<? } ?>> <label for="cf_use_map">사용</label></td>
            </tr>
            <tr>
                <th scope="row"><label for="cf_use_map_all">전체 지역</label></th>
                <td>
                    <input type="checkbox" name="cf_use_map_all" value="1" id="cf_use_map_all" <? if($config['cf_use_map_all']) { ?>checked<? } ?>> <label for="cf_use_map_all">전체 지도 사용</label>
                    <p class="frm_info">전체 지도 사용 시, 최초 지역 진입 때 전체 지도가 출력됩니다. 이후 상위 지역을 선택할 경우 상위 지역으로 이동됩니다.</p>
                </td>
            </tr>
            <? if($config['cf_use_map_all']) { ?>
            <tr>
                <th scope="row">전체 지도 설정</th>
                <td>
                    W: <input type="text" name="cf_map_all_w" value="<?php echo get_text($config['cf_map_all_w']) ?>" class="frm_input" style="width:50px;"> px
                    &nbsp;&nbsp;
                    H: <input type="text" name="cf_map_all_h" value="<?php echo get_text($config['cf_map_all_h']) ?>" class="frm_input" style="width:50px;"> px
                    <div style="margin-top:10px;">
                        <strong>이미지 첨부</strong>
                        <div style="display:block; position:relative; width:200px; height:100px; border:1px solid #ddd; background:#f5f5f5; margin:5px 0;">
                            <? if($config['cf_map_all_img']) { ?>
                                <img src="<?=$config['cf_map_all_img']?>" style="display:block; width:100%; height:100%; object-fit:cover;" onerror="this.remove();"/>
                            <? } ?>
                        </div>
                        <input type="file" name="cf_map_all_img_file">
                        <input type="hidden" name="cf_map_all_img" value="<?php echo get_text($config['cf_map_all_img']) ?>">
                        <?php if($config['cf_map_all_img']) { ?>
                        <input type="checkbox" name="cf_map_all_img_del" value="1" id="cf_map_all_img_del"> <label for="cf_map_all_img_del">기존 이미지 삭제</label>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <? } ?>
            </tbody>
            </table>
            <div class="btn_confirm01 btn_confirm">
                <input type="submit" value="설정 저장" class="btn_submit">
            </div>
        </div>
    </form>
    
    <hr class="padding_20">

    <h2 class="h2_frm">신규 지역 추가</h2>
    <form name="fpointlist2" method="post" id="fpointlist2" action="./map_update.php" autocomplete="off">
        <input type="hidden" name="token" value="<?php echo $token ?>">
        <div class="tbl_frm01 tbl_wrap">
            <table>
                <colgroup>
                    <col style="width:120px;">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th scope="row"><label for="ma_parent">소속될 지역</label></th>
                    <td>
                        <select id="ma_parent" name="ma_parent">
                            <option value="">최상위 카테고리로 생성</option>
                        <?
                            $map_list_data_for_select = array();
                            $map_sql_for_select = "select ma_id, ma_parent, ma_name from {$g5['map_table']} order by ma_parent asc, ma_id asc";
                            $map_result_for_select = sql_query($map_sql_for_select);
                            while($row_for_select = sql_fetch_array($map_result_for_select)) {
                                $parent = ($row_for_select['ma_parent'] == $row_for_select['ma_id']) ? 0 : $row_for_select['ma_parent'];
                                $map_list_data_for_select[$parent][] = $row_for_select;
                            }

                            function display_map_options($parent_id, $level = 0) {
                                global $map_list_data_for_select;
                                if(!isset($map_list_data_for_select[$parent_id])) return;

                                $indent = str_repeat("&nbsp;&nbsp;", $level);
                                foreach($map_list_data_for_select[$parent_id] as $row) {
                                    // 하위 지역(level 2)은 자식을 가질 수 없으므로 선택지에서 제외
                                    if ($level < 2) {
                                        echo "<option value='{$row['ma_id']}'>{$indent}┗ {$row['ma_name']}</option>";
                                        display_map_options($row['ma_id'], $level + 1);
                                    }
                                }
                            }
                            display_map_options(0);
                        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ma_name">지역명</label></th>
                    <td>
                        <input type="text" name="ma_name" value="" id="ma_name" class="required frm_input" required placeholder="지역명 입력">
                        &nbsp;&nbsp;
                        <input type="checkbox" name="ma_use" value="1" id="ma_use" checked>
                        <label for="ma_use">사용</label>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="btn_confirm01 btn_confirm">
            <input type="submit" value="신규 지역 추가" class="btn_submit">
        </div>
    </form>
</div>
<hr class="padding_20">
<div>
    <h2 class="h2_frm">지역 목록</h2>
    <section id="anc_001">
        <div class="local_ov01 local_ov">
            <?php echo $listall ?>
            전체 <?php echo number_format($total_count) ?> 건
        </div>
        <form name="fpointlist" id="fpointlist" method="post" action="./map_list_update.php" onsubmit="return fpointlist_submit(this);" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?php echo $token ?>">
        <div class="tbl_head01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <colgroup>
                <col style="width: 45px" /><col style="width: 50px" /><col style="width: 50px" /><col /><col style="width: 50px;" /><col style="width: 50px;" /><col style="width: 50px;" /><col style="width: 70px;" /><col style="width: 70px;" /><col style="width: 80px;" /><col style="width: 80px;"/><col style="width: 110px;" /><col style="width: 70px;"/>
            </colgroup>
            <thead>
            <tr>
                <th scope="col"><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
                <th>ID</th><th>상세</th><th scope="col">지역명</th><th scope="col">사용</th><th scope="col" title="캐릭터 생성 시 최초로 시작할 지역을 설정합니다.">시작</th><th scope="col" title="던전이 열릴 수 있는 지역을 체크합니다.">던전</th><th>X</th><th>Y</th><th>W</th><th>H</th><th>이벤트 방식</th><th>이벤트</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $map_list_data = array();
            $map_sql = "select * from {$g5['map_table']} order by ma_parent asc, ma_id asc";
            $map_result = sql_query($map_sql);
            while($row = sql_fetch_array($map_result)) {
                $parent = ($row['ma_parent'] == $row['ma_id']) ? 0 : $row['ma_parent'];
                $map_list_data[$parent][] = $row;
            }

            function display_map_rows($parent_id, $level = 0) {
                global $g5, $config, $map_list_data, $colspan;
                if(!isset($map_list_data[$parent_id])) return;

                foreach($map_list_data[$parent_id] as $row) {
                    $bg = 'bg'.($i%2);
                    $indent = str_repeat("┗… ", $level);
                    $is_parent_type = ($level < 2); // Level 0(카테고리)과 Level 1(상위 지역)은 부모 타입으로 간주

            ?>
            <tr class="<?php echo $bg; ?>">
                <td class="td_chk"><input type="checkbox" name="chk[]" value="<?php echo $row['ma_id'] ?>" id="chk_<?php echo $row['ma_id'] ?>"></td>
                <td class="td_num"><?php echo $row['ma_id'] ?></td>
                <td><a href="javascript:;" onclick="$(this).closest('tr').next().toggle();" class="btn btn_03" style="padding: 0 5px; height:22px; line-height:22px;">+</a></td>
                <td class="td_left">
                    <?php echo $indent; ?>
                    <input type="text" name="ma_name[<?php echo $row['ma_id'] ?>]" value="<?php echo get_text($row['ma_name']) ?>" required class="required frm_input" size="20">
                    <button type="button" onclick="open_coords_picker('<?php echo $row['ma_id'] ?>')" class="btn_frmline" style="margin-left:5px;">위치 설정</button>
                </td>
                <td class="td_chk"><input type="checkbox" name="ma_use[<?php echo $row['ma_id'] ?>]" value="1" <?php echo $row['ma_use']?"checked":"" ?>></td>
                
                <td class="td_chk"><?php if (!$is_parent_type) { ?><input type="checkbox" name="ma_start[<?php echo $row['ma_id'] ?>]" value="1" <?php echo $row['ma_start']?"checked":"" ?>><?php } ?></td>
                <td class="td_chk"><?php if (!$is_parent_type) { ?><input type="checkbox" name="ma_use_dungeon[<?php echo $row['ma_id'] ?>]" value="1" <?php echo $row['ma_use_dungeon']?"checked":"" ?>><?php } ?></td>
                
                <td>
                    <input type="text" name="ma_left[<?php echo $row['ma_id'] ?>]" value="<?php echo get_text($row['ma_left']) ?>" id="ma_left_<?php echo $row['ma_id'] ?>" class="frm_input" size="5">
                </td>
                <td>
                    <input type="text" name="ma_top[<?php echo $row['ma_id'] ?>]" value="<?php echo get_text($row['ma_top']) ?>" id="ma_top_<?php echo $row['ma_id'] ?>" class="frm_input" size="5">
                </td>

                <td><?php if ($is_parent_type) { ?><input type="text" name="ma_width[<?php echo $row['ma_id'] ?>]" value="<?php echo get_text($row['ma_width']) ?>" class="frm_input" size="5"><?php } else { ?><input type="hidden" name="ma_width[<?php echo $row['ma_id'] ?>]" value="<?php echo get_text($row['ma_width']) ?>" /><?php } ?></td>
                <td><?php if ($is_parent_type) { ?><input type="text" name="ma_height[<?php echo $row['ma_id'] ?>]" value="<?php echo get_text($row['ma_height']) ?>" class="frm_input" size="5"><?php } else { ?><input type="hidden" name="ma_height[<?php echo $row['ma_id'] ?>]" value="<?php echo get_text($row['ma_height']) ?>" /><?php } ?></td>
                
                <td class="td_mng">
                    <?php if(!$is_parent_type) { ?>
                    <select name="ma_event_type[<?php echo $row['ma_id'] ?>]">
                        <option value="random" <?php if(isset($row['ma_event_type']) && $row['ma_event_type'] == 'random') echo 'selected'; ?>>확률 탐색</option>
                        <option value="keyword" <?php if(isset($row['ma_event_type']) && $row['ma_event_type'] == 'keyword') echo 'selected'; ?>>키워드 탐색</option>
                    </select>
                    <?php } ?>
                </td>
                <td class="td_mng">
                    <?php if(!$is_parent_type) { 
                            $me_cnt = sql_fetch("select count(me_id) as cnt from {$g5['map_event_table']} where ma_id = '{$row['ma_id']}'");
                            $me_cnt = $me_cnt['cnt'];
                    ?>
                        <a href="./map_event_list.php?ma_id=<?=$row['ma_id']?>" class="btn btn_02"><?=$me_cnt?>건</a>
                    <?php } ?>
                </td>
            </tr>
            <tr class="<?php echo $bg; ?>" style="display:none;">
                <td></td>
                <td colspan="<?php echo $colspan; ?>" class="td_left" style="padding:10px;">
                    <?php if($is_parent_type) { ?>
                    <div style="margin-bottom: 10px;">
                        <strong>지도 이미지</strong>
                        <div style="display:block; position:relative; width:200px; height:100px; border:1px solid #ddd; background:#f5f5f5; margin:5px 0;">
                            <? if($row['ma_img']) { ?><img src="<?=$row['ma_img']?>" style="display:block; width:100%; height:100%; object-fit:cover;" /><? } ?>
                        </div>
                        <input type="file" name="ma_img_file[<?php echo $row['ma_id'] ?>]">
                        <input type="hidden" name="ma_img[<?php echo $row['ma_id'] ?>]" value="<?php echo get_text($row['ma_img']) ?>">
                        <?php if ($row['ma_img']) { ?>
                        <input type="checkbox" name="ma_img_del[<?php echo $row['ma_id'] ?>]" value="1" id="ma_img_del_<?php echo $row['ma_id']; ?>">
                        <label for="ma_img_del_<?php echo $row['ma_id']; ?>">기존 이미지 삭제</label>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    <textarea name="ma_content[<?php echo $row['ma_id'] ?>]" class="frm_input" style="width:100%; height:125px;"><?php echo get_text($row['ma_content']) ?></textarea>
                </td>
            </tr>
            <?php
                    display_map_rows($row['ma_id'], $level + 1);
                }
            }
            display_map_rows(0);

            if (empty($map_list_data))
                echo '<tr><td colspan="'.($colspan+1).'" class="empty_table">자료가 없습니다.</td></tr>';
            ?>
            </tbody>
            </table>
        </div>
        <div class="btn_list01 btn_list">
            <input type="submit" name="act_button" value="선택 수정" onclick="document.pressed=this.value">
            <input type="submit" name="act_button" value="선택 삭제" onclick="document.pressed=this.value">
        </div>
        </form>
    </section>
</div>
<script>
function fpointlist_submit(f) {
	if (!is_checked("chk[]")) {
		alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
		return false;
	}
	if(document.pressed == "선택삭제") {
		if(!confirm("선택한 자료를 정말 삭제하시겠습니까? (하위 지역이 있는 경우 삭제할 수 없습니다)")) {
			return false;
		}
	}
	return true;
}

// 팝업창에서 선택한 좌표를 입력 필드에 넣는 함수
function set_map_coords(ma_id, x, y) {
    document.getElementById('ma_left_' + ma_id).value = x;
    document.getElementById('ma_top_' + ma_id).value = y;
}

// 좌표 선택 팝업 열기
function open_coords_picker(ma_id) {
    // 부모 지역의 이미지를 기준으로 좌표를 잡아야 하므로 
    // 실제로는 '전체 맵 이미지'나 '부모 지역 이미지'를 띄워야 합니다.
    window.open('./map_coords_picker.php?ma_id=' + ma_id, 'coords_picker', 'width=1000, height=800, scrollbars=yes');
}

</script>
<?php
include_once ('./admin.tail.php');
?>