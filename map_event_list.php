<?php
$sub_menu = "710100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');
$token = get_token();

$ma_id = isset($_REQUEST['ma_id']) ? (int)$_REQUEST['ma_id'] : 0;
$me_id = isset($_REQUEST['me_id']) ? (int)$_REQUEST['me_id'] : 0;
$w     = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';

$ma = sql_fetch("select * from {$g5['map_table']} where ma_id = '{$ma_id}'");

if(!$ma['ma_id']) { 
	alert("지역 정보를 확인할 수 없습니다.");
}

// 수정일 경우, 이벤트 정보 불러오기
$me = array();
if ($w == 'u' && $me_id) {
    $me = sql_fetch("select * from {$g5['map_event_table']} where me_id = '{$me_id}'");
}

$sql_common = " from {$g5['map_event_table']} where ma_id = '{$ma_id}' ";
$sql_order = " order by me_id asc";

$sql = " select count(*) as cnt {$sql_common} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 20;
$total_page  = ceil($total_count / $rows);
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $rows;

$sql = " select * {$sql_common} {$sql_order} limit {$from_record}, {$rows}";
$result = sql_query($sql);

$g5['title'] = "[ ".$ma['ma_name']." ] 지역 이벤트 관리";
include_once ('./admin.head.php');

$colspan = 11;
?>

<div class="groupWrap" style="min-width:1400px;">
	<div class="form-area">
		<h2 class="h2_frm">이벤트 <?php echo ($w == 'u') ? '수정' : '생성'; ?></h2>
		<form name="feventform" id="feventform" action="./map_event_form_update.php" onsubmit="return fshopform_submit(this)" method="post">
			<input type="hidden" name="w" value="<?php echo $w ?>">
			<input type="hidden" name="me_id" value="<?php echo $me_id ?>">
			<input type="hidden" name="ma_id" value="<?php echo $ma_id ?>">
			<input type="hidden" name="page" value="<?php echo $page ?>">
			<input type="hidden" name="token" value="<?php echo $token ?>">

			<div class="tbl_frm01 tbl_wrap">
				<table>
				<colgroup>
					<col style="width: 120px;">
					<col>
				</colgroup>
				<tbody>
				<tr>
					<th scope="row"><label for="me_title">이벤트명</label></th>
					<td>
						<input type="text" name="me_title" value="<?php echo get_text($me['me_title']) ?>" id="me_title" required class="required frm_input" size="50">
						<input type="checkbox" name="me_use" id="me_use" value="1" <?php echo (isset($me['me_use']) && $me['me_use'] == 1) || $w == '' ? "checked" : ""?>>
						<label for="me_use">사용</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="me_content">내용</label></th>
					<td>
						<textarea name="me_content" id="me_content" class="full-width" rows="5"><?php echo get_text($me['me_content']) ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row">확률 구간</th>
					<td>
						<?php echo help("맵의 이벤트 방식이 '확률 탐색'일 경우 사용됩니다. 100면체 주사위를 굴렸을 때의 범위를 지정합니다.") ?>
						<input type="text" name="me_per_s" value="<?php echo $me['me_per_s']; ?>" id="me_per_s" class="frm_input" size="5">
						~
						<input type="text" name="me_per_e" value="<?php echo $me['me_per_e']; ?>" id="me_per_e" class="frm_input" size="5">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="me_keyword">키워드</label></th>
					<td>
						<?php echo help("맵의 이벤트 방식이 '키워드 탐색'일 경우 사용됩니다. 여러 개일 경우 | 로 구분하세요.") ?>
						<input type="text" name="me_keyword" value="<?php echo get_text($me['me_keyword']); ?>" id="me_keyword" class="frm_input" size="40">
					</td>
				</tr>
				<tr>
					<th scope="row">획득 횟수<br>(현재/최대)</th>
					<td>
						<?php echo help("최대 획득 횟수를 제한합니다. 0으로 설정 시 무제한으로 획득할 수 있습니다.") ?>
						<input type="text" name="me_now_cnt" value="<?php echo (int)$me['me_now_cnt']?>" class="frm_input" size="10"> / 
						<input type="text" name="me_replay_cnt" value="<?php echo (int)$me['me_replay_cnt']?>" class="frm_input" size="10">
					</td>
				</tr>
				<tr>
					<th scope="row">획득 아이템</th>
					<td>
						<input type="text" name="it_name" value="<?php echo get_item_name($me['me_get_item'])?>" class="frm_input" size="30">
					</td>
				</tr>
				<tr>
					<th scope="row">획득 <?=$config['cf_money']?></th>
					<td>
						<input type="text" name="me_get_money" value="<?php echo (int)$me['me_get_money']?>" class="frm_input" size="15"> <?=$config['cf_money_pice']?>
					</td>
				</tr>
				</tbody>
				</table>
			</div>

			<div class="btn_confirm01 btn_confirm">
				<input type="submit" value="확인" class="btn_submit" accesskey="s">
			</div>
		</form>
	</div>
    <hr class="padding_20">
	<div>
		<div class="local_ov01 local_ov">
			<a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?ma_id=<?php echo $ma_id; ?>" class="ov_listall">전체 목록</a>
			전체 <?php echo number_format($total_count) ?> 건
		</div>

		<form name="fpointlist" id="fpointlist" method="post" action="./map_event_list_update.php" onsubmit="return fpointlist_submit(this);">
			<input type="hidden" name="page" value="<?php echo $page ?>">
			<input type="hidden" name="ma_id" value="<?php echo $ma_id ?>">
			<input type="hidden" name="token" value="<?php echo $token ?>">
			<div class="tbl_head01 tbl_wrap">
				<table>
				<caption><?php echo $g5['title']; ?> 목록</caption>
				<colgroup>
					<col style="width: 45px">
					<col>
					<col style="width: 150px">
					<col style="width: 120px">
					<col style="width: 120px">
					<col style="width: 50px">
					<col style="width: 70px">
				</colgroup>
				<thead>
				<tr>
					<th scope="col"><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
					<th scope="col">이벤트명</th>
					<th scope="col">키워드</th>
					<th scope="col">확률 구간</th>
					<th scope="col">획득 현황(현재/최대)</th>
					<th scope="col">사용</th>
					<th scope="col">관리</th>
				</tr>
				</thead>
				<tbody>
				<?php for ($i=0; $row=sql_fetch_array($result); $i++) { ?>
				<tr class="<?php echo 'bg'.($i%2); ?>">
					<td class="td_chk">
						<input type="hidden" name="me_id[<?php echo $i ?>]" value="<?php echo $row['me_id'] ?>">
						<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
					</td>
					<td class="td_left">
						<input type="text" name="me_title[<?php echo $i ?>]" value="<?php echo get_text($row['me_title']) ?>" class="frm_input full">
					</td>
					<td class="td_left">
						<input type="text" name="me_keyword[<?php echo $i ?>]" value="<?php echo get_text($row['me_keyword']) ?>" class="frm_input full">
					</td>
					<td>
						<input type="text" name="me_per_s[<?php echo $i ?>]" value="<?php echo $row['me_per_s'] ?>" class="frm_input" style="width:45%; text-align:center;"> ~
						<input type="text" name="me_per_e[<?php echo $i ?>]" value="<?php echo $row['me_per_e'] ?>" class="frm_input" style="width:45%; text-align:center;">
					</td>
					<td>
						<input type="text" name="me_now_cnt[<?php echo $i ?>]" value="<?php echo $row['me_now_cnt'] ?>" class="frm_input" style="width:45%; text-align:center;"> /
						<input type="text" name="me_replay_cnt[<?php echo $i ?>]" value="<?php echo $row['me_replay_cnt'] ?>" class="frm_input" style="width:45%; text-align:center;">
					</td>
					<td class="td_chk">
						<input type="checkbox" name="me_use[<?php echo $i ?>]" value="1" <?php echo $row['me_use']?'checked':''; ?>>
					</td>
					<td class="td_mng">
						<a href="?w=u&me_id=<?php echo $row['me_id']; ?>&ma_id=<?php echo $ma_id; ?>&page=<?php echo $page; ?>" class="btn btn_03">수정</a>
					</td>
				</tr>
				<?php }
				if ($i == 0) {
					echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
				}
				?>
				</tbody>
				</table>
			</div>

			<div class="btn_list01 btn_list">
				<input type="submit" name="act_button" value="선택 수정" onclick="document.pressed=this.value">
				<input type="submit" name="act_button" value="선택 삭제" onclick="document.pressed=this.value">
				<a href="./map_list.php" class="btn btn_02">지역 관리</a>
			</div>
		</form>
		<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?ma_id='.$ma_id.'&amp;page='); ?>
	</div>
</div>

<style>
.groupWrap { display: block; }
.groupWrap > .form-area { width: 48%; float: left; }
.groupWrap > div:not(.form-area) { width: 50%; float: right; }
</style>

<script>
function fpointlist_submit(f) {
	if (!is_checked("chk[]")) {
		alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
		return false;
	}
	if(document.pressed == "선택삭제") {
		if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
			return false;
		}
	}
	return true;
}
</script>

<?php
include_once ('./admin.tail.php');
?>