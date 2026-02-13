<?php
$sub_menu = "710400";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '탐색 로그';
include_once('./admin.head.php');

$sql_common = " FROM {$g5['map_log']} l LEFT JOIN {$g5['character_table']} c ON (l.ch_id = c.ch_id) ";

$sql_search = "";
if ($stx) {
    $sql_search .= " WHERE c.ch_name LIKE '%{$stx}%' OR l.ml_log LIKE '%{$stx}%' ";
}

$sql_order = " ORDER BY l.ml_id DESC ";

$sql = " SELECT count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $rows;

$sql = " SELECT l.*, c.ch_name {$sql_common} {$sql_search} {$sql_order} LIMIT {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '<a href="'.$_SERVER['PHP_SELF'].'" class="ov_listall">전체 목록</a>';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    전체 <?php echo number_format($total_count); ?>건
</div>

<form name="fsearch" id="fsearch" class="local_sch01 local_sch" method="get">
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <colgroup>
        <col style="width: 150px;">
        <col>
        <col style="width: 180px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">캐릭터</th>
        <th scope="col">로그 내용</th>
        <th scope="col">시간</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_name"><?php echo get_text($row['ch_name']); ?></td>
        <td class="td_left"><?php echo get_text($row['ml_log']); ?></td>
        <td><?php echo $row['ml_datetime']; ?></td>
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="3" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once ('./admin.tail.php');
?>