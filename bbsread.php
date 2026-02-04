<?php

// Coptright 2025-2026 satohina This is Message By Not Delete
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$data_dir = __DIR__ . '/data';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta name="viewport" content="width=device-width">
  <meta charset="UTF-8">
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: sans-serif;
      display: flex;
      flex-direction: column;
    }

    .form-area {
      padding: 10px 15px;
      background: #f9f9f9;
      border-bottom: 1px solid #ccc;
    }

    .thread-list {
      flex: 1;
      overflow-y: auto;
      padding: 10px 15px;
      background: #fff;
    }

    ul {
      padding-left: 20px;
      margin: 0;
    }

    li {
      margin-bottom: 8px;
    }

    .time {
      font-size: 0.8em;
      color: #888;
    }

    input, textarea {
      width: 100%;
      box-sizing: border-box;
      margin-bottom: 5px;
    }
  </style>
</head>
<body>

<div class="form-area">
  <h4>新規スレ作成</h4>
  <form method="post" action="bbs.php" target="_top">
    <input type="hidden" name="mode" value="create">
    タイトル:<br><input type="text" name="title" required><br>
    名前:<br><input type="text" name="name" required><br>
    コメント:<br><textarea name="comment" rows="2" required></textarea><br>
    <button type="submit">作成</button>
  </form>
</div>

<div class="thread-list">
  <h3>スレッド一覧</h3>
  <ul>
    <?php
    $files = glob("$data_dir/*.json");
usort($files, function($a, $b){
    return filemtime($b) - filemtime($a);
});

    foreach ($files as $file) {
        $id = basename($file, '.json');
        $thread = json_decode(file_get_contents($file), true);
        $first = $thread['posts'][0] ?? null;
        echo "<li><a href='bbsmobile.php?thread=$id' target='_top'>" . h($thread['title']) . "</a><br>";
        echo "<span class='time'>[" . ($first['time'] ?? '') . "]</span></li>";
    }
    ?>
  </ul>
</div>

</body>
</html>
