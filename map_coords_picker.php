<?php
include_once('./_common.php');

$ma_id = (int)$_GET['ma_id'];
$me = sql_fetch("select * from {$g5['map_table']} where ma_id = '$ma_id'");

// 1. 부모 정보 가져오기
$parent = sql_fetch("select * from {$g5['map_table']} where ma_id = '{$me['ma_parent']}'");

// 2. 배경 이미지 결정
if ($me['ma_id'] == $me['ma_parent']) {
    // 본인이 최상위라면 '전체 지도'
    $target_img = $config['cf_map_all_img'];
} else {
    // 본인이 하위 지역이라면 '부모의 이미지' 우선
    $target_img = ($parent['ma_img']) ? $parent['ma_img'] : $config['cf_map_all_img'];
}

// [경로가 상대경로라면 URL을 붙여줌
if($target_img && !preg_match("~^(http|https|//)~i", $target_img)) {
    $target_img = G5_URL . str_replace(G5_PATH, '', $target_img);
}

if (!$target_img) {
    echo "<script>alert('기준 이미지가 없습니다. 상위 지역에 이미지를 등록하거나 전체 지도를 먼저 등록해 주세요.'); window.close();</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>좌표 선택기</title>
    <style>
        body { margin:0; padding:0; cursor: crosshair; }
        .map-container { position: relative; display: inline-block; }
        .info-box { position: fixed; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: #fff; padding: 10px; border-radius: 5px; pointer-events: none; }
    </style>
</head>
<body>

<div class="info-box">지도 위를 클릭하면 좌표가 자동으로 입력됩니다.</div>

<div class="map-container">
    <img src="<?php echo $target_img; ?>" id="target_map">
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#target_map').on('click', function(e) {
    // 이미지 내 상대 좌표 계산
    var offset = $(this).offset();
    var x = Math.round(e.pageX - offset.left);
    var y = Math.round(e.pageY - offset.top);

    if(confirm("선택하신 좌표 (X: " + x + ", Y: " + y + ")로 설정하시겠습니까?")) {
        // 부모 창의 함수 호출하여 값 전달
        window.opener.set_map_coords('<?php echo $ma_id; ?>', x, y);
        window.close();
    }
});
</script>

</body>
</html>