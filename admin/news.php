<?php
session_start();
$admin_password = 'fleurir-admin';

if (isset($_POST['logout'])) { unset($_SESSION['admin_logged_in']); }
if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) { $_SESSION['admin_logged_in'] = true; }
    else { $login_error = 'パスワードが違います'; }
}

if (!isset($_SESSION['admin_logged_in'])) {
    ?><!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>管理画面ログイン | FLEURIR</title><style>body{font-family:sans-serif;background:#f5f3f0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.login-box{background:#fff;padding:40px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);text-align:center}h1{color:#9F886E;margin-bottom:20px}input{padding:12px;width:200px;border:1px solid #ddd;border-radius:5px;margin-bottom:15px}button{background:#9F886E;color:#fff;padding:12px 30px;border:none;border-radius:5px;cursor:pointer}button:hover{background:#8a7560}.error{color:#c00;margin-bottom:15px}</style></head><body><div class="login-box"><h1>FLEURIR 管理画面</h1><?php if(isset($login_error)) echo '<p class="error">'.$login_error.'</p>'; ?><form method="post"><input type="password" name="password" placeholder="パスワード" required><br><button type="submit">ログイン</button></form></div></body></html><?php exit;
}

$message = '';

// お知らせ投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_news'])) {
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $date = date('Y-m-d');
    $display_date = date('Y.m.d');

    if ($title && $excerpt && $content) {
        // ファイル名を生成
        $slug = 'news-' . date('Y-m-d-His');
        $filename = $slug . '.html';

        // 画像アップロード処理
        $image_html = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed)) {
                $new_filename = $slug . '.' . $ext;
                $upload_path = '../images/news/' . $new_filename;

                // newsフォルダがなければ作成
                if (!is_dir('../images/news')) {
                    mkdir('../images/news', 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $image_path = 'images/news/' . $new_filename;
                    $image_html = '
        <div class="news-article-image">
            <img src="../' . $image_path . '" alt="' . htmlspecialchars($title) . '">
        </div>';
                }
            }
        }

        // お知らせ記事HTMLを生成
        $news_html = '<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="' . htmlspecialchars($excerpt) . '">
    <title>' . htmlspecialchars($title) . ' | FLEURIR お知らせ</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Noto+Sans+JP:wght@300;400;500&family=Noto+Serif+JP:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .news-article { max-width: 800px; margin: 0 auto; padding: 40px 20px; }
        .news-article-header { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .news-article-meta { display: flex; gap: 15px; margin-bottom: 15px; }
        .news-article-meta time { color: #888; font-size: 14px; }
        .news-category { background: #9F886E; color: #fff; padding: 3px 10px; border-radius: 3px; font-size: 12px; }
        .news-article-title { font-size: 24px; color: #5a4a3a; line-height: 1.5; }
        .news-article-image { margin: 30px 0; text-align: center; }
        .news-article-image img { max-width: 100%; height: auto; border-radius: 10px; }
        .news-article-content { line-height: 2; color: #5a4a3a; }
        .news-nav { text-align: center; margin-top: 40px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <div class="logo">
                <a href="../index.html">
                    <h1>FLEURIR</h1>
                    <span class="logo-tagline">Eye Lash & Facial Wax Salon</span>
                </a>
            </div>
            <nav class="nav">
                <button class="nav-toggle" aria-label="メニューを開く">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <ul class="nav-menu">
                    <li><a href="../about.html">About<span>サロン紹介</span></a></li>
                    <li><a href="../service.html">Service<span>サービス</span></a></li>
                    <li><a href="../menu.html">Price<span>料金</span></a></li>
                    <li><a href="../voice.html">Voice<span>お客様の声</span></a></li>
                    <li><a href="../blog/index.html">Blog<span>ブログ</span></a></li>
                    <li><a href="../products.html">Products<span>商品</span></a></li>
                    <li><a href="../index.html#contact">Access<span>アクセス</span></a></li>
                    <li class="nav-reservation"><a href="../index.html#reservation">ご予約</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="page-header">
        <div class="container">
            <span class="page-label">News</span>
            <h1 class="page-title">お知らせ</h1>
        </div>
    </section>

    <main class="news-article">
        <div class="news-article-header">
            <div class="news-article-meta">
                <time datetime="' . $date . '">' . $display_date . '</time>
                <span class="news-category">お知らせ</span>
            </div>
            <h1 class="news-article-title">' . htmlspecialchars($title) . '</h1>
        </div>' . $image_html . '
        <div class="news-article-content">
            ' . nl2br(htmlspecialchars($content)) . '
        </div>
        <div class="news-nav">
            <a href="../news.html" class="btn btn-secondary">お知らせ一覧へ戻る</a>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>FLEURIR</h2>
                    <p>Eye Lash & Facial Wax Salon</p>
                </div>
                <nav class="footer-nav">
                    <ul>
                        <li><a href="../about.html">About</a></li>
                        <li><a href="../service.html">Service</a></li>
                        <li><a href="../menu.html">Price</a></li>
                        <li><a href="../voice.html">Voice</a></li>
                        <li><a href="../blog/index.html">Blog</a></li>
                        <li><a href="../products.html">Products</a></li>
                        <li><a href="../index.html#contact">Access</a></li>
                    </ul>
                </nav>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 FLEURIR. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    <script src="../js/script.js"></script>
</body>
</html>';

        // お知らせ記事を保存
        if (file_put_contents('../news/' . $filename, $news_html)) {
            // お知らせ一覧を更新
            $index_file = '../news.html';
            $index_content = file_get_contents($index_file);

            // 新しい記事のエントリーを作成
            $new_entry = '                <article class="news-item-full">
                    <div class="news-item-header">
                        <time class="news-date">' . $display_date . '</time>
                        <span class="news-category">お知らせ</span>
                    </div>
                    <h2 class="news-title-full"><a href="news/' . $filename . '">' . htmlspecialchars($title) . '</a></h2>
                    <p class="news-excerpt">' . htmlspecialchars($excerpt) . '</p>
                </article>

';

            // 既存の記事リストの先頭に追加
            $index_content = preg_replace(
                '/(<div class="news-list-full">)\s*(<article)/s',
                '$1' . "\n" . $new_entry . '$2',
                $index_content
            );

            file_put_contents($index_file, $index_content);

            $message = '<div class="success">お知らせを投稿しました！ <a href="../news/' . $filename . '" target="_blank">記事を確認</a></div>';
        } else {
            $message = '<div class="error">投稿に失敗しました</div>';
        }
    } else {
        $message = '<div class="error">タイトル、抜粋、本文は必須です</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お知らせ投稿 | FLEURIR</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Hiragino Sans', sans-serif; background: #f5f3f0; }
        .header { background: #9F886E; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 20px; }
        .nav { background: #fff; padding: 15px; border-bottom: 1px solid #ddd; }
        .nav a { padding: 10px 20px; margin-right: 10px; background: #f5f3f0; color: #5a4a3a; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 5px; }
        .nav a:hover, .nav a.active { background: #9F886E; color: #fff; }
        .container { max-width: 900px; margin: 20px auto; padding: 0 20px; }
        .card { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h2 { color: #9F886E; margin-bottom: 20px; font-size: 18px; border-bottom: 2px solid #f5f3f0; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #5a4a3a; }
        input[type="text"], input[type="file"], textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 15px; }
        textarea { height: 200px; resize: vertical; font-family: inherit; }
        .textarea-small { height: 80px; }
        .btn { background: #9F886E; color: #fff; padding: 15px 40px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #8a7560; }
        .btn-logout { background: #999; padding: 8px 15px; font-size: 13px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success a { color: #155724; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .help { font-size: 13px; color: #888; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FLEURIR お知らせ投稿</h1>
        <form method="post" style="display:inline;">
            <button type="submit" name="logout" class="btn btn-logout">ログアウト</button>
        </form>
    </div>

    <div class="nav">
        <a href="edit.php">サイト編集</a>
        <a href="images.php">画像管理</a>
        <a href="blog.php">ブログ投稿</a>
        <a href="news.php" class="active">お知らせ投稿</a>
        <a href="index.html">マニュアル</a>
    </div>

    <div class="container">
        <?php echo $message; ?>

        <div class="card">
            <h2>新規お知らせ投稿</h2>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>タイトル *</label>
                    <input type="text" name="title" placeholder="例：年末年始の営業について" required>
                </div>

                <div class="form-group">
                    <label>画像（任意）</label>
                    <input type="file" name="image" accept="image/*">
                    <p class="help">記事内に表示されます。JPG, PNG, GIF, WEBP対応</p>
                </div>

                <div class="form-group">
                    <label>抜粋（一覧に表示される文章）*</label>
                    <textarea name="excerpt" class="textarea-small" placeholder="お知らせ一覧に表示される短い説明文を入力してください" required></textarea>
                    <p class="help">100文字程度が目安です</p>
                </div>

                <div class="form-group">
                    <label>本文 *</label>
                    <textarea name="content" placeholder="お知らせの本文を入力してください" required></textarea>
                    <p class="help">改行はそのまま反映されます</p>
                </div>

                <button type="submit" name="post_news" class="btn">投稿する</button>
            </form>
        </div>
    </div>
</body>
</html>
