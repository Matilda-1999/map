<?php
$sub_menu = "710300";
include_once('./_common.php');
$ma_id = isset($_REQUEST['ma_id']) ? (int)$_REQUEST['ma_id'] : 0;

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '캐릭터 위치 관리';
include_once('./admin.head.php');

// 지역 목록
$ma_config = array();
$ma_list = array();
$ma_sql = "select ma_id, ma_name, ma_parent from {$g5['map_table']} where ma_use = 1 order by ma_parent asc, ma_id asc";
$ma_result = sql_query($ma_sql);

$now_map = null;
$now_parent = "";

$map_tree = array();
while($map = sql_fetch_array($ma_result)) {
    $ma_config[$map['ma_id']] = $map;
    $parent = ($map['ma_parent'] == $map['ma_id']) ? 0 : $map['ma_parent'];
    $map_tree[$parent][] = $map;
}

if($ma_id) {
    $now_map = $ma_config[$ma_id];
    $now_parent_id = $now_map['ma_parent'];
    $now_parent = isset($ma_config[$now_parent_id]) ? $ma_config[$now_parent_id]['ma_name'] : '';
}

function display_map_menu_for_member($parent_id, $level) {
    global $map_tree, $ma_id;
    if(!isset($map_tree[$parent_id])) return;

    echo "<ul>";
    foreach($map_tree[$parent_id] as $map) {
        $is_parent = isset($map_tree[$map['ma_id']]);
        if($is_parent) {
            echo "<li><p>{$map['ma_name']}</p>";
            display_map_menu_for_member($map['ma_id'], $level + 1);
            echo "</li>";
        } else {
            $class = ($map['ma_id'] == $ma_id) ? "selected" : "";
            echo "<li><a href='?ma_id={$map['ma_id']}' class='{$class}'>{$map['ma_name']}</a></li>";
        }
    }
    echo "</ul>";
}
?>
<div class="mapMoveLayout">
	<div class="mapList">
		<?php display_map_menu_for_member(0, 0); ?>
	</div>
	<div class="mapMoveList">
		<? if(!$ma_id) { ?>
		<div class="none">
			<p>위치를 관리할 지역을 선택해 주세요.</p>
		</div>
		<? } else {
			$ch_list = array();
			$ch_not_list = array();
			$ch_result = sql_query("select ch_id, ch_name, ma_id from {$g5['character_table']} where ch_state='승인' order by ch_name asc");
			while($ch = sql_fetch_array($ch_result)) {
				if($ch['ma_id'] != $ma_id) {
					$ch_not_list[] = $ch;
				} else {
					$ch_list[] = $ch;
				}
			}
		?>
		<form name="fconfigform" id="fconfigform" method="post" action="./map_member_list_update.php" onsubmit="return fconfigform_submit(this);">
			<input type="hidden" name="token" value="<?php echo $token ?>" id="token">
			<input type="hidden" name="ma_id" value="<?=$ma_id?>" />
			<h2><?=$now_parent?> : <?=$now_map['ma_name']?></h2>
			<div class="map-setting-wrap">
				<div class="ch-list-box map-left-box">
					<div class="scroll-box">
						<div class="data-filter">
							<div class="input-search">
								<input type="text" class="sch-text" placeholder="임관명 검색" />
								<button type="button">검색</button>
							</div>
							<button type="button" class="chk-all"><span>현재 목록 전체 선택</span></button>
						</div>

						<ul class="data-filter-list">
							<? foreach($ch_list as $ch) { ?>
								<li data-name="<?=$ch['ch_name']?>">
									<input type="checkbox" name="ch_expert[]" value="<?=$ch['ch_id']?>" id="ch_expert_<?=$ch['ch_id']?>" class="chk-filter" />
									<label for="ch_expert_<?=$ch['ch_id']?>">
										<strong><?=$ch['ch_name']?></strong>
									</label>
								</li>
							<? } ?>
						</ul>
					</div>
				</div>
				<div class="control">
					<div class="btn_list01 btn_list">
						<input type="submit" name="act_button" value="《 지역 이동" onclick="document.pressed=this.value">
						<br /><br />
						<input type="submit" name="act_button" value="지역 이탈 》" onclick="document.pressed=this.value" style="background:#d99898;">
					</div>
				</div>
				<div class="ch-list-box map-right-box">
					<div class="scroll-box">
						<div class="data-filter">
							<div class="input-search">
								<input type="text" class="sch-text" placeholder="임관명/지역명 검색" />
								<button type="button">검색</button>
							</div>
							<button type="button" class="chk-all"><span>현재 목록 전체 선택</span></button>
						</div>
						<ul class="data-filter-list">
							<? foreach($ch_not_list as $ch) {
								$ch_map_pa_name = $ma_config[$ch['ma_id']] ? ($ma_config[$ma_config[$ch['ma_id']]['ma_parent']]['ma_name'] ?: '') : '미지정';
								$ch_map_name = $ma_config[$ch['ma_id']] ? $ma_config[$ch['ma_id']]['ma_name'] : '';
								$full_map_name = $ch_map_pa_name.($ch_map_name ? ' &gt; '.$ch_map_name : '');
							?>
								<li data-name="<?=$ch['ch_name']?>" data-map="<?=$full_map_name?>">
									<input type="checkbox" name="ch_insert[]" value="<?=$ch['ch_id']?>" id="ch_insert_<?=$ch['ch_id']?>" class="chk-filter" />
									<label for="ch_insert_<?=$ch['ch_id']?>">
										<strong><?=$ch['ch_name']?></strong><span><?=$full_map_name?></span>
									</label>
								</li>
							<? } ?>
						</ul>
					</div>
				</div>
			</div>
		</form>
		<? } ?>
	</div>
</div>

<script>
$(function() {
    function filterList($list_box) {
        let keyword = $list_box.find('.sch-text').val().toLowerCase();
        $list_box.find('.data-filter-list li').each(function() {
            let name = $(this).data('name').toLowerCase();
            let map = $(this).data('map') ? $(this).data('map').toLowerCase() : '';
            if (name.includes(keyword) || map.includes(keyword)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

	$('.data-filter .input-search button').on('click', function() {
		let $list_box = $(this).closest('.ch-list-box');
        filterList($list_box);
	});
    $('.data-filter .input-search .sch-text').on('keyup', function(e) {
        if (e.keyCode == 13) {
            let $list_box = $(this).closest('.ch-list-box');
            filterList($list_box);
        }
    });

	$('.data-filter .chk-all').on('click', function() {
		let $filterList = $(this).closest('.ch-list-box');
		let is_all_checked = true;
		$filterList.find('.data-filter-list li:visible .chk-filter').each(function() {
			if (!$(this).prop('checked')) {
				is_all_checked = false;
				return false;
			}
		});

		$filterList.find('.data-filter-list li:visible .chk-filter').prop('checked', !is_all_checked);
	});
});

function fconfigform_submit(f) {
	if ((document.pressed == "《 지역 이동" && !$("input[name='ch_insert[]']:checked").length) ||
	    (document.pressed == "지역 이탈 》" && !$("input[name='ch_expert[]']:checked").length)) {
		alert("이동할 캐릭터를 하나 이상 선택하세요.");
		return false;
	}
	f.action = "./map_member_list_update.php";
	return true;
}
</script>

<style>
ul,li {margin:0; padding:0; list-style:none;}
.mapMoveLayout {display:flex; gap: 15px;}
.mapList {width: 200px; border:1px solid #dadada; background:#fafafa; padding: 10px; align-self: flex-start;}
.mapList ul ul { padding-left: 15px; border-left: 1px solid #eee; margin-left: 5px;}
.mapList li { padding: 2px 0; }
.mapList p { font-weight: bold; padding: 5px; }
.mapList a { display: block; padding: 8px 10px; border-radius: 4px; }
.mapList a:hover { background: #e9e9e9; }
.mapList a.selected { background: #337ab7; color:#fff; font-weight: 800; }
.mapMoveList {flex: 1;}
.mapMoveList .none {display:flex; justify-content:center; align-items:center; height:400px; border:1px dashed #ddd; border-radius:10px; color:#ccc;}
.map-setting-wrap {display:flex; width:100%; max-width:900px; gap:10px; align-items:center;}
.map-setting-wrap > * {display:block;}
.map-setting-wrap .control {width:120px; text-align:center;}
.ch-list-box { flex: 1; }
.scroll-box {border:1px solid #dadada; border-radius:10px; background:#fafafa; padding:10px;}
.scroll-box ul {display:block; padding:5px; height:calc(100vh - 280px); min-height: 400px; overflow:auto;}
.data-filter {margin-bottom:10px;}
.input-search {display:flex; margin-bottom:5px;}
.input-search .sch-text {flex:1; border-right:none;}
.chk-all {width:100%;}
.map-setting-wrap li {display:block; border-top:1px solid #eaeaea;}
.map-setting-wrap li:first-child { border-top: none; }
.map-setting-wrap li label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 5px 10px 25px;
    position: relative;
    cursor: pointer;
}
.map-setting-wrap li input[type="checkbox"] {position:absolute; left:5px; top:50%; transform:translateY(-50%);}
.map-setting-wrap li label strong { font-weight: normal; }
.map-setting-wrap li label span {
    background: #555;
    color: #fff;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 0.9em;
    margin-left: 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 60%;
}
</style>

<?php
include_once ('./admin.tail.php');
?>