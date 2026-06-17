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

// 画像アップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $target = $_POST['target'] ?? '';
    $upload_dir = '../images/';

    // ターゲットに応じてサブディレクトリを設定
    if (strpos($target, 'menu_') === 0) {
        $upload_dir = '../images/menu/';
    } elseif (strpos($target, 'blog_') === 0) {
        $upload_dir = '../images/blog/';
    }

    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed) && $file['error'] === 0) {
        // 新しいファイル名を生成
        $new_filename = 'upload_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // HTMLファイルの画像パスを更新
            $image_path = str_replace('../', '', $upload_path);

            // ターゲットに応じてHTMLを更新
            $updated = false;

            // TOPスライダー
            if ($target === 'hero_1' || $target === 'hero_2' || $target === 'hero_3') {
                $css_file = '../css/style.css';
                $css_content = file_get_contents($css_file);
                $slide_num = substr($target, -1);

                if ($slide_num == '1') {
                    $css_content = preg_replace(
                        '/(\.slide:nth-child\(1\)\s*\{[^}]*background-image:\s*url\()[^)]+(\))/s',
                        '${1}\'../' . $image_path . '\'${2}',
                        $css_content
                    );
                } elseif ($slide_num == '2') {
                    $css_content = preg_replace(
                        '/(\.slide:nth-child\(2\)\s*\{[^}]*background-image:\s*url\()[^)]+(\))/s',
                        '${1}\'../' . $image_path . '\'${2}',
                        $css_content
                    );
                } elseif ($slide_num == '3') {
                    $css_content = preg_replace(
                        '/(\.slide:nth-child\(3\)\s*\{[^}]*background-image:\s*url\()[^)]+(\))/s',
                        '${1}\'../' . $image_path . '\'${2}',
                        $css_content
                    );
                }

                if (file_put_contents($css_file, $css_content)) {
                    $updated = true;
                }
            }

            // メニュー画像（menu.html）
            elseif (strpos($target, 'menu_') === 0) {
                $menu_file = '../menu.html';
                $menu_content = file_get_contents($menu_file);

                $image_map = [
                    'menu_eyelash_1' => 'まつ毛エクステ1枚目',
                    'menu_eyelash_2' => 'まつ毛エクステ2枚目',
                    'menu_cosmelift' => 'コスメリフト',
                    'menu_eyebrow' => '眉WAX',
                    'menu_facial' => 'フェイシャルWAX',
                ];

                // 画像の置換ロジック（セクションIDで特定）
                if ($target === 'menu_eyelash_1') {
                    $menu_content = preg_replace(
                        '/(id="eyelash".*?<div class="menu-image-item">\s*<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $menu_content, 1
                    );
                } elseif ($target === 'menu_eyelash_2') {
                    // 2枚目の画像を探す
                    $menu_content = preg_replace(
                        '/(id="eyelash".*?<div class="menu-image-item">\s*<img[^>]+>.*?<div class="menu-image-item">\s*<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $menu_content, 1
                    );
                } elseif ($target === 'menu_cosmelift') {
                    $menu_content = preg_replace(
                        '/(id="cosmelift".*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $menu_content, 1
                    );
                } elseif ($target === 'menu_eyebrow') {
                    $menu_content = preg_replace(
                        '/(id="eyebrow-wax".*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $menu_content, 1
                    );
                } elseif ($target === 'menu_facial') {
                    $menu_content = preg_replace(
                        '/(id="facial-wax".*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $menu_content, 1
                    );
                }

                if (file_put_contents($menu_file, $menu_content)) {
                    $updated = true;
                }
            }

            // サービスページ画像
            elseif (strpos($target, 'service_') === 0) {
                $service_file = '../service.html';
                $service_content = file_get_contents($service_file);

                if ($target === 'service_cosmelift') {
                    $service_content = preg_replace(
                        '/(id="cosmelift".*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $service_content, 1
                    );
                } elseif ($target === 'service_flatlash') {
                    $service_content = preg_replace(
                        '/(id="flatlash".*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $service_content, 1
                    );
                } elseif ($target === 'service_facial') {
                    $service_content = preg_replace(
                        '/(id="facial-wax".*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $service_content, 1
                    );
                }

                if (file_put_contents($service_file, $service_content)) {
                    $updated = true;
                }
            }

            // TOPページメニュー画像
            elseif (strpos($target, 'top_menu_') === 0) {
                $index_file = '../index.html';
                $index_content = file_get_contents($index_file);

                if ($target === 'top_menu_eyelash') {
                    $index_content = preg_replace(
                        '/(menu-category-grid.*?まつ毛エクステ.*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $index_content, 1
                    );
                } elseif ($target === 'top_menu_cosmelift') {
                    $index_content = preg_replace(
                        '/(menu-category-grid.*?コスメリフト.*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $index_content, 1
                    );
                } elseif ($target === 'top_menu_eyebrow') {
                    $index_content = preg_replace(
                        '/(menu-category-grid.*?眉WAX.*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $index_content, 1
                    );
                } elseif ($target === 'top_menu_facial') {
                    $index_content = preg_replace(
                        '/(menu-category-grid.*?フェイシャル.*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $index_content, 1
                    );
                }

                if (file_put_contents($index_file, $index_content)) {
                    $updated = true;
                }
            }

            // 商品ページ画像
            elseif (strpos($target, 'product_') === 0) {
                $products_file = '../products.html';
                $products_content = file_get_contents($products_file);

                $product_names = [
                    'product_1' => 'ラッシュアディクト',
                    'product_2' => 'バミルアイリッドセラム',
                    'product_3' => '寿光美 スキンケアセット',
                    'product_4' => 'プラセンタドリンク激',
                    'product_5' => 'AOサプリプラス',
                    'product_6' => 'ノイクリアコーティング',
                    'product_7' => 'V3',
                ];

                $product_num = substr($target, -1);
                $product_name = $product_names[$target] ?? '';

                if ($product_name) {
                    $products_content = preg_replace(
                        '/(<article class="product-card">.*?' . preg_quote($product_name, '/') . '.*?<img src=")[^"]+(")/s',
                        '${1}' . $image_path . '${2}',
                        $products_content, 1
                    );

                    if (file_put_contents($products_file, $products_content)) {
                        $updated = true;
                    }
                }
            }

            if ($updated) {
                $message = '<div class="success">画像をアップロードしました！<br>新しい画像: ' . $image_path . '</div>';
            } else {
                $message = '<div class="success">画像をアップロードしました: ' . $image_path . '<br>（HTMLの更新に失敗した可能性があります）</div>';
            }
        } else {
            $message = '<div class="error">アップロードに失敗しました</div>';
        }
    } else {
        $message = '<div class="error">無効なファイル形式です（JPG, PNG, GIF, WEBPのみ）</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>画像管理 | FLEURIR</title>
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
        select, input[type="file"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 15px; }
        .btn { background: #9F886E; color: #fff; padding: 15px 40px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #8a7560; }
        .btn-logout { background: #999; padding: 8px 15px; font-size: 13px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .help { font-size: 13px; color: #888; margin-top: 5px; }
        .image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
        .image-item { border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
        .image-item img { width: 100%; height: 150px; object-fit: cover; }
        .image-item p { padding: 10px; font-size: 12px; color: #666; word-break: break-all; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FLEURIR 画像管理</h1>
        <form method="post" style="display:inline;">
            <button type="submit" name="logout" class="btn btn-logout">ログアウト</button>
        </form>
    </div>

    <div class="nav">
        <a href="edit.php">サイト編集</a>
        <a href="images.php" class="active">画像管理</a>
        <a href="blog.php">ブログ投稿</a>
        <a href="news.php">お知らせ投稿</a>
        <a href="index.html">マニュアル</a>
    </div>

    <div class="container">
        <?php echo $message; ?>

        <div class="card">
            <h2>画像をアップロード</h2>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>変更する場所</label>
                    <select name="target" required>
                        <optgroup label="TOPページ スライダー">
                            <option value="hero_1">スライダー 1枚目</option>
                            <option value="hero_2">スライダー 2枚目</option>
                            <option value="hero_3">スライダー 3枚目</option>
                        </optgroup>
                        <optgroup label="TOPページ 料金メニュー">
                            <option value="top_menu_eyelash">まつ毛エクステ</option>
                            <option value="top_menu_cosmelift">コスメリフト</option>
                            <option value="top_menu_eyebrow">眉WAXスタイリング</option>
                            <option value="top_menu_facial">フェイシャルWAX</option>
                        </optgroup>
                        <optgroup label="料金ページ（menu.html）">
                            <option value="menu_eyelash_1">まつ毛エクステ 1枚目</option>
                            <option value="menu_eyelash_2">まつ毛エクステ 2枚目</option>
                            <option value="menu_cosmelift">コスメリフト</option>
                            <option value="menu_eyebrow">眉WAXスタイリング</option>
                            <option value="menu_facial">フェイシャルWAX</option>
                        </optgroup>
                        <optgroup label="サービスページ（service.html）">
                            <option value="service_cosmelift">コスメリフト</option>
                            <option value="service_flatlash">フラットマットラッシュ</option>
                            <option value="service_facial">フェイシャルWAX</option>
                        </optgroup>
                        <optgroup label="商品ページ（products.html）">
                            <option value="product_1">ラッシュアディクト</option>
                            <option value="product_2">バミルアイリッドセラム</option>
                            <option value="product_3">寿光美 スキンケアセット</option>
                            <option value="product_4">プラセンタドリンク激</option>
                            <option value="product_5">AOサプリプラス</option>
                            <option value="product_6">ノイクリアコーティング</option>
                            <option value="product_7">V3ファンデーション</option>
                        </optgroup>
                    </select>
                </div>

                <div class="form-group">
                    <label>画像ファイル</label>
                    <input type="file" name="image" accept="image/*" required>
                    <p class="help">JPG, PNG, GIF, WEBP形式（推奨サイズ: 1200x800px以上）</p>
                </div>

                <button type="submit" class="btn">アップロード</button>
            </form>
        </div>

        <div class="card">
            <h2>アップロード済み画像</h2>
            <div class="image-grid">
                <?php
                $images = glob('../images/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                $images = array_merge($images, glob('../images/menu/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE));
                usort($images, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $images = array_slice($images, 0, 12);

                foreach ($images as $img):
                    $path = str_replace('../', '', $img);
                ?>
                <div class="image-item">
                    <img src="../<?php echo $path; ?>" alt="">
                    <p><?php echo $path; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
