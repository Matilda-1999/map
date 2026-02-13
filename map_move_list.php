<?php
$sub_menu = "710200";
include_once('./_common.php');

$ma_id = isset($_REQUEST['ma_id']) ? (int)$_REQUEST['ma_id'] : 0;

auth_check($auth[$sub_menu], 'r');
$token = get_token();

$g5['title'] = "지역별 통행 관리";
include_once ('./admin.head.php');

// 모든 지역 정보를 가져와 계층 구조로 미리 정렬
$map_list_data = array();
$map_sql = "select ma_id, ma_parent, ma_name from {$g5['map_table']} order by ma_parent asc, ma_id asc";
$map_result = sql_query($map_sql);
while($row = sql_fetch_array($map_result)) {
    $parent = ($row['ma_parent'] == $row['ma_id']) ? 0 : $row['ma_parent'];
    $map_list_data[$parent][] = $row;
}

// 재귀적으로 지역 목록 메뉴를 출력하는 함수
function display_map_menu($parent_id, $level = 0) {
    global $map_list_data, $ma_id;
    if(!isset($map_list_data[$parent_id])) return;

    $indent_str = str_repeat('· ', $level * 2);
    $is_parent = isset($map_list_data[$row['ma_id']]);

    foreach($map_list_data[$parent_id] as $row) {
        $is_child_parent = isset($map_list_data[$row['ma_id']]);
        $class = ($row['ma_id'] == $ma_id) ? "selected" : "";
        
        // 최하위 지역만 링크 활성화
        if (!$is_child_parent) {
            echo "<li><a href='?ma_id={$row['ma_id']}' class='{$class}'>{$indent_str}{$row['ma_name']}</a></li>";
        } else {
            echo "<li><p class='parent-map' style='padding-left:".($level*15)."px;'>{$row['ma_name']}</p><ul>";
            display_map_menu($row['ma_id'], $level + 1);
            echo "</ul></li>";
        }
    }
}

// 현재 선택된 출발지 맵 정보
$current_map = $ma_id ? sql_fetch("select ma_id, ma_name from {$g5['map_table']} where ma_id = '{$ma_id}'") : null;
$current_map_parent = $current_map ? get_map_parnet_name($current_map['ma_id']) : '';

// 현재 설정된 이동 규칙 불러오기
$move_rules = array();
if ($ma_id) {
    $rules_result = sql_query("select mr_to_ma_id, mr_keyword from {$g5['map_move_rules_table']} where mr_from_ma_id = '{$ma_id}'");
    while ($rule = sql_fetch_array($rules_result)) {
        $move_rules[$rule['mr_to_ma_id']] = $rule['mr_keyword'];
    }
}
?>

<style>
.mapMoveLayout { display: flex; gap: 20px; }
.mapList { width: 250px; border: 1px solid #ddd; background: #fafafa; padding: 10px; }
.mapList ul { list-style: none; margin: 0; padding: 0; }
.mapList li { margin-bottom: 5px; }
.mapList .parent-map { font-weight: bold; padding: 8px 5px; background: #eee; border-radius: 4px; }
.mapList ul ul { margin-left: 10px; margin-top: 5px; }
.mapList a { display: block; padding: 8px 10px; text-decoration: none; color: #333; border-radius: 4px; }
.mapList a:hover { background: #e9e9e9; }
.mapList a.selected { background: #337ab7; color: #fff; font-weight: bold; }

.mapMoveList { flex: 1; }
.mapMoveList .none { display: flex; justify-content: center; align-items: center; height: 400px; border: 1px dashed #ccc; border-radius: 5px; color: #999; }
.mapMoveList h2 { margin-top: 0; border-bottom: 2px solid #333; padding-bottom: 10px; }
.destination-list { list-style: none; padding: 0; margin: 0; }
.destination-list > li { padding: 10px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 15px; }
.destination-list > li:nth-child(odd) { background: #f9f9f9; }
.destination-list .dest-name { font-weight: bold; width: 200px; }
.destination-list .dest-name .parent-name { font-weight: normal; color: #777; font-size: 0.9em; display: block; }
.destination-list .keyword-input { width: 200px; }
</style>

<div class="mapMoveLayout">
	<div class="mapList">
		<ul>
			<?php display_map_menu(0); ?>
		</ul>
	</div>

	<div class="mapMoveList">
		<?php if(!$ma_id) { ?>
		<div class="none">
			<p>통행 설정을 할 출발 지역을 왼쪽에서 선택해 주세요.</p>
		</div>
		<?php } else { ?>
		<h2>출발지: <?=$current_map_parent?> &gt; <?=$current_map['ma_name']?></h2>
        <?php echo help("이동을 허용할 지역을 체크하고, 필요하다면 이동에 필요한 키워드를 입력하세요. 키워드가 없으면 자유롭게 이동할 수 있습니다."); ?>
		<form name="fmoveform" id="fmoveform" method="post" action="./map_move_update.php">
			<input type="hidden" name="token" value="<?php echo $token ?>">
			<input type="hidden" name="ma_id" value="<?=$ma_id?>">
			
            <ul class="destination-list">
            <?php
            // 모든 하위맵 목록을 목적지로 표시 (자기 자신 제외)
            $all_maps_result = sql_query("select ma_id, ma_name, ma_parent from {$g5['map_table']} where ma_id != ma_parent and ma_id != '{$ma_id}' order by ma_parent, ma_id");
            while ($dest_map = sql_fetch_array($all_maps_result)) {
                $dest_parent_name = get_map_parnet_name($dest_map['ma_id']);
                $is_checked = array_key_exists($dest_map['ma_id'], $move_rules);
                $keyword = $is_checked ? $move_rules[$dest_map['ma_id']] : '';
            ?>
                <li>
                    <input type="checkbox" name="dest_maps[]" value="<?php echo $dest_map['ma_id']; ?>" id="dest_<?php echo $dest_map['ma_id']; ?>" <?php echo $is_checked ? 'checked' : ''; ?>>
                    <label for="dest_<?php echo $dest_map['ma_id']; ?>" class="dest-name">
                        <?php echo $dest_map['ma_name']; ?>
                        <span class="parent-name"><?php echo $dest_parent_name; ?></span>
                    </label>
                    <label for="keyword_<?php echo $dest_map['ma_id']; ?>">키워드:</label>
                    <input type="text" name="keywords[<?php echo $dest_map['ma_id']; ?>]" id="keyword_<?php echo $dest_map['ma_id']; ?>" value="<?php echo get_text($keyword); ?>" class="frm_input keyword-input">
                </li>
            <?php
            }
            ?>
            </ul>

			<div class="btn_confirm01 btn_confirm">
				<input type="submit" value="저장" class="btn_submit" accesskey="s">
			</div>
		</form>
		<?php } ?>
	</div>
</div>

<?php
include_once ('./admin.tail.php');
?>