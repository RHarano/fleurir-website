<?php
// パスワード認証
session_start();
$admin_password = 'fleurir-admin';

if (isset($_POST['logout'])) {
    unset($_SESSION['admin_logged_in']);
}

if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = 'パスワードが違います';
    }
}

if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>管理画面ログイン | FLEURIR</title>
        <style>
            body { font-family: sans-serif; background: #f5f3f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .login-box { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
            h1 { color: #9F886E; margin-bottom: 20px; }
            input { padding: 12px; width: 200px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 15px; }
            button { background: #9F886E; color: #fff; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; }
            button:hover { background: #8a7560; }
            .error { color: #c00; margin-bottom: 15px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>FLEURIR 管理画面</h1>
            <?php if (isset($login_error)) echo '<p class="error">'.$login_error.'</p>'; ?>
            <form method="post">
                <input type="password" name="password" placeholder="パスワード" required><br>
                <button type="submit">ログイン</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$message = '';

// ブログ削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_blog'])) {
    $file_to_delete = basename($_POST['delete_blog']); // セキュリティ対策
    $blog_file = '../blog/' . $file_to_delete;

    if (file_exists($blog_file) && $file_to_delete !== 'index.html') {
        // 記事ファイルを削除
        if (unlink($blog_file)) {
            // ブログ一覧から該当記事を削除
            $index_file = '../blog/index.html';
            $index_content = file_get_contents($index_file);

            // 該当記事のエントリーを削除（正規表現で該当記事ブロックを削除）
            $pattern = '/<article class="blog-list-item"[^>]*>\s*<a href="' . preg_quote($file_to_delete, '/') . '"[\s\S]*?<\/article>\s*/';
            $index_content = preg_replace($pattern, '', $index_content);

            file_put_contents($index_file, $index_content);

            // TOPページ（トップの最新ブログ一覧）からも該当記事を削除
            $top_file = '../index.html';
            if (file_exists($top_file)) {
                $top_content = file_get_contents($top_file);
                // TOPページのリンクは blog/ファイル名 になっている
                $top_pattern = '/<article class="blog-card">\s*<a href="blog\/' . preg_quote($file_to_delete, '/') . '"[\s\S]*?<\/article>\s*/';
                $top_content = preg_replace($top_pattern, '', $top_content);
                file_put_contents($top_file, $top_content);
            }

            // 関連画像も削除（存在する場合）
            $slug = pathinfo($file_to_delete, PATHINFO_FILENAME);
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            foreach ($image_extensions as $ext) {
                $image_file = '../images/blog/' . $slug . '.' . $ext;
                if (file_exists($image_file)) {
                    unlink($image_file);
                }
            }

            $message = '<div class="success">ブログ記事「' . htmlspecialchars($file_to_delete) . '」を削除しました</div>';
        } else {
            $message = '<div class="error">削除に失敗しました</div>';
        }
    } else {
        $message = '<div class="error">ファイルが見つかりません</div>';
    }
}

// ブログ投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_blog'])) {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? 'ビューティー';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $date = date('Y-m-d');
    $display_date = date('Y.m.d');

    if ($title && $excerpt && $content) {
        // ファイル名を生成
        $slug = 'blog-' . date('Y-m-d-His');
        $filename = $slug . '.html';

        // 画像アップロード処理
        $image_path = 'images/blog/default.jpg'; // デフォルト画像
        $image_html = ''; // 記事内の画像HTML

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed)) {
                $new_filename = $slug . '.' . $ext;
                $upload_path = '../images/blog/' . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $image_path = 'images/blog/' . $new_filename;
                    // 記事内に画像を追加
                    $image_html = '
                    <div class="blog-article-image">
                        <img src="../' . $image_path . '" alt="' . htmlspecialchars($title) . '">
                    </div>';
                }
            }
        }

        // ブログ記事HTMLを生成
        $blog_html = '<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="' . htmlspecialchars($excerpt) . '">
    <title>' . htmlspecialchars($title) . ' | FLEURIR ブログ</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Noto+Sans+JP:wght@300;400;500&family=Noto+Serif+JP:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .blog-article-image { margin: 30px 0; text-align: center; }
        .blog-article-image img { max-width: 100%; height: auto; border-radius: 10px; }
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
                    <li><a href="index.html" class="active">Blog<span>ブログ</span></a></li>
                    <li><a href="../products.html">Products<span>商品</span></a></li>
                    <li><a href="../index.html#contact">Access<span>アクセス</span></a></li>
                    <li class="nav-reservation"><a href="../index.html#reservation">ご予約</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Blog</h1>
            <p class="page-subtitle">ブログ</p>
        </div>
    </section>

    <main class="blog-single">
        <div class="container">
            <article class="blog-article">
                <div class="blog-article-header">
                    <div class="blog-article-meta">
                        <time datetime="' . $date . '">' . $display_date . '</time>
                        <span class="blog-category">' . htmlspecialchars($category) . '</span>
                    </div>
                    <h1 class="blog-article-title">' . htmlspecialchars($title) . '</h1>
                </div>' . $image_html . '
                <div class="blog-article-content">
                    ' . nl2br(htmlspecialchars($content)) . '
                </div>
            </article>
            <div class="blog-nav">
                <a href="index.html" class="btn btn-secondary">ブログ一覧へ戻る</a>
            </div>
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
                        <li><a href="index.html">Blog</a></li>
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

        // ブログ記事を保存
        if (file_put_contents('../blog/' . $filename, $blog_html)) {
            // ブログ一覧を更新
            $index_file = '../blog/index.html';
            $index_content = file_get_contents($index_file);

            // 新しい記事のエントリーを作成
            $new_entry = '                        <article class="blog-list-item" data-category="' . htmlspecialchars($category) . '">
                            <a href="' . $filename . '">
                                <div class="blog-list-image">
                                    <img src="../' . $image_path . '" alt="' . htmlspecialchars($title) . '" loading="lazy">
                                </div>
                                <div class="blog-list-info">
                                    <div class="blog-list-meta">
                                        <time datetime="' . $date . '">' . $display_date . '</time>
                                        <span class="blog-category">' . htmlspecialchars($category) . '</span>
                                    </div>
                                    <h2 class="blog-list-title">' . htmlspecialchars($title) . '</h2>
                                    <p class="blog-list-excerpt">' . htmlspecialchars($excerpt) . '</p>
                                </div>
                            </a>
                        </article>

';

            // 既存の記事リストの先頭に追加
            $index_content = preg_replace(
                '/(<div class="blog-list">)\s*(<article)/s',
                '$1' . "\n" . $new_entry . '$2',
                $index_content
            );

            file_put_contents($index_file, $index_content);

            $message = '<div class="success">ブログを投稿しました！ <a href="../blog/' . $filename . '" target="_blank">記事を確認</a></div>';
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
    <title>ブログ投稿 | FLEURIR</title>
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
        input[type="text"], input[type="file"], textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 15px; }
        textarea { height: 200px; resize: vertical; font-family: inherit; }
        .textarea-small { height: 80px; }
        .btn { background: #9F886E; color: #fff; padding: 15px 40px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #8a7560; }
        .btn-logout { background: #999; padding: 8px 15px; font-size: 13px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success a { color: #155724; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .help { font-size: 13px; color: #888; margin-top: 5px; }
        .blog-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .blog-table th, .blog-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .blog-table th { background: #f5f3f0; font-weight: bold; color: #5a4a3a; }
        .blog-table td a { color: #9F886E; }
        .blog-table tr:hover { background: #faf9f7; }
        .btn-delete { background: #dc3545; color: #fff; padding: 6px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-delete:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FLEURIR ブログ投稿</h1>
        <form method="post" style="display:inline;">
            <button type="submit" name="logout" class="btn btn-logout">ログアウト</button>
        </form>
    </div>

    <div class="nav">
        <a href="edit.php">サイト編集</a>
        <a href="images.php">画像管理</a>
        <a href="blog.php" class="active">ブログ投稿</a>
        <a href="news.php">お知らせ投稿</a>
        <a href="index.html">マニュアル</a>
    </div>

    <div class="container">
        <?php echo $message; ?>

        <div class="card">
            <h2>新規ブログ投稿</h2>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>タイトル *</label>
                    <input type="text" name="title" placeholder="例：お客さま日記＊マツエク" required>
                </div>

                <div class="form-group">
                    <label>カテゴリー</label>
                    <select name="category">
                        <option value="ビューティー">ビューティー</option>
                        <option value="サロン紹介">サロン紹介</option>
                        <option value="お知らせ">お知らせ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>画像（任意）</label>
                    <input type="file" name="image" accept="image/*">
                    <p class="help">一覧と記事の両方に表示されます。JPG, PNG, GIF, WEBP対応</p>
                </div>

                <div class="form-group">
                    <label>抜粋（一覧に表示される文章）*</label>
                    <textarea name="excerpt" class="textarea-small" placeholder="ブログ一覧に表示される短い説明文を入力してください" required></textarea>
                    <p class="help">100文字程度が目安です</p>
                </div>

                <div class="form-group">
                    <label>本文 *</label>
                    <textarea name="content" placeholder="ブログの本文を入力してください" required></textarea>
                    <p class="help">改行はそのまま反映されます</p>
                </div>

                <button type="submit" name="post_blog" class="btn">投稿する</button>
            </form>
        </div>

        <div class="card">
            <h2>ブログ記事一覧・削除</h2>
            <p class="help" style="margin-bottom: 15px;">削除したい記事の「削除」ボタンをクリックしてください。削除は取り消せません。</p>

            <table class="blog-table">
                <thead>
                    <tr>
                        <th>ファイル名</th>
                        <th>タイトル</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $blog_dir = '../blog/';
                    $files = glob($blog_dir . '*.html');
                    rsort($files); // 新しい順

                    foreach ($files as $file) {
                        $filename = basename($file);
                        if ($filename === 'index.html') continue; // 一覧ページは除外

                        // タイトルを取得
                        $content = file_get_contents($file);
                        preg_match('/<title>([^<]+)<\/title>/', $content, $matches);
                        $title = isset($matches[1]) ? str_replace(' | FLEURIR ブログ', '', $matches[1]) : $filename;
                        $title = str_replace(' | FLEURIR', '', $title);
                        ?>
                        <tr>
                            <td><a href="../blog/<?php echo htmlspecialchars($filename); ?>" target="_blank"><?php echo htmlspecialchars($filename); ?></a></td>
                            <td><?php echo htmlspecialchars($title); ?></td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirm('「<?php echo htmlspecialchars($title); ?>」を削除しますか？\nこの操作は取り消せません。');">
                                    <button type="submit" name="delete_blog" value="<?php echo htmlspecialchars($filename); ?>" class="btn-delete">削除</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }

                    if (count($files) <= 1) {
                        echo '<tr><td colspan="3" style="text-align:center;color:#888;">ブログ記事がありません</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
