<?php
// Copyright 2025-2026 satohina PHPBBS This is Message By Not Delete
function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$data_dir = __DIR__ . '/data';
if (!is_dir($data_dir)) mkdir($data_dir);



// ID生成（IP + UA + 日付）
function generate_user_id() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $date = date('Ymd');
    return substr(hash('sha1', $ip . $ua . $date), 0, 8);
}

// スレ作成
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['mode'] === 'create') {
    $title = h($_POST['title']);
    $name = h($_POST['name']);
    $comment = h($_POST['comment']);
    $now = date("Y/m/d H:i:s");
    $id = time();
    $uid = generate_user_id();
    $thread = [
        'title' => $title,
        'posts' => [
            ['name' => $name, 'comment' => $comment, 'time' => $now, 'uid' => $uid]
        ]
    ];
    file_put_contents("$data_dir/$id.json", json_encode($thread, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header("Location: bbs.php?thread=$id");
    exit;
}

// レス投稿
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['mode'] === 'reply') {
    $id = basename($_POST['thread']);
    $file = "$data_dir/$id.json";
    if (file_exists($file)) {
        $thread = json_decode(file_get_contents($file), true);
        if (count($thread['posts']) >= 500) {
            http_response_code(410);
            echo "<h2>このスレッドは閲覧できなくなりました。閲覧したい場合は管理者にお問い合わせフォームからスレッドをリクエストしてください</h2>";
            exit;
        }
        $thread['posts'][] = [
            'name' => h($_POST['name']),
            'comment' => h($_POST['comment']),
            'time' => date("Y/m/d H:i:s"),
            'uid' => generate_user_id()
        ];
        file_put_contents($file, json_encode($thread, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    header("Location: bbs.php?thread=$id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>KakeCh</title>
  <style>
    body { margin: 0; display: flex; height: 100vh; font-family: sans-serif; }
    iframe {
      width: 300px;
      height: 100%;
      border: none;
      border-right: 1px solid #ccc;
    }
    #main {
      flex-grow: 1;
      padding: 20px;
      overflow-y: auto;
    }
    .post { border-top: 1px solid #ccc; padding: 10px 0; }
    .time { font-size: 0.9em; color: #888; }
    .uid { font-size: 0.9em; color: #088; margin-left: 8px; }
  </style>
  <script>
// スクロール位置を保存して復元
document.addEventListener("DOMContentLoaded", () => {
  const key = "scroll_" + location.href;

  // 復元
  const savedY = sessionStorage.getItem(key);
  if (savedY !== null) window.scrollTo(0, parseInt(savedY));

  // 自動保存
  window.addEventListener("scroll", () => {
    sessionStorage.setItem(key, window.scrollY);
  });

  // 15秒ごとにリロード
  setTimeout(() => {
    sessionStorage.setItem(key, window.scrollY);
    location.reload();
  }, 15000);
});
</script>
<script>
if (/iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
  window.location.href = "bbsread.php"; // リダイレクト先URL
}
</script>
</head>
<body>
  <iframe src="list.php"></iframe>

  <div id="main">
    <?php if (isset($_GET['thread'])): ?>
      <?php
        $id = basename($_GET['thread']);
        $file = "$data_dir/$id.json";
        if (!file_exists($file)) {
            echo "<p>スレッドが存在しません。</p>";
            exit;
        }
        $thread = json_decode(file_get_contents($file), true);
        $is_dropped = count($thread['posts']) >= 500;
      ?>
      <h2><?php echo h($thread['title']); ?><?php if ($is_dropped) echo "（dat落ち）"; ?></h2>
      <?php foreach ($thread['posts'] as $i => $p): ?>
        <div class="post">
          <strong><?php echo $i+1; ?> 名前：<?php echo h($p['name']); ?>
          <span class="uid">ID:<?php echo h($p['uid'] ?? '???????'); ?></span></strong>
          <span class="time">[<?php echo $p['time']; ?>]</span><br>
          <?php echo nl2br(h($p['comment'])); ?>
        </div>
      <?php endforeach; ?>

      <?php if (!$is_dropped): ?>
      <h3>レス投稿</h3>
      <form method="post">
        <input type="hidden" name="mode" value="reply">
        <input type="hidden" name="thread" value="<?php echo $id; ?>">
        名前:<br><input type="text" name="name" required><br>
        コメント:<br><textarea name="comment" rows="4" required></textarea><br>
        <button type="submit">投稿</button>
      </form>
      <?php endif; ?>
    <?php else: ?>
      <h2>スレッドを選択してください</h2>
    <?php endif; ?>
  </div>
</body>
</html>
