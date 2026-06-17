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

// ====== 保存処理 ======

// メニュー価格
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_menu'])) {
    $file = '../menu.html';
    $content = file_get_contents($file);
    $replacements = [
        'eyelash_80'=>'80本','eyelash_100'=>'100本','eyelash_120'=>'120本','eyelash_140'=>'140本','eyelash_unlimited'=>'2時間付け放題',
        'cosmelift'=>'コスメリフト</span>','cosmelift_lower'=>'コスメリフト下',
        'eyebrow'=>'眉WAXスタイリング</span>',
        'facial'=>'フェイシャルWAX</span>','facial_part'=>'部分WAX</span>','facial_nape'=>'うなじ',
    ];
    foreach ($replacements as $field => $label) {
        if (!empty($_POST[$field])) {
            $price = preg_replace('/[^0-9]/', '', $_POST[$field]);
            $content = preg_replace('/(<span class="price-label">'.preg_quote($label,'/').'.*?<span class="price-value">)¥[0-9]+/s', '$1¥'.$price, $content);
        }
    }
    file_put_contents($file, $content);
    $message = '<div class="success">メニュー価格を保存しました</div>';
}

// 営業情報
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_info'])) {
    $file = '../index.html';
    $content = file_get_contents($file);
    if (!empty($_POST['hours'])) {
        $content = preg_replace('/(<h4>営業時間<\/h4>\s*<p>)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['hours']).'${2}', $content);
    }
    if (!empty($_POST['holiday'])) {
        $content = preg_replace('/(<h4>定休日<\/h4>\s*<p>)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['holiday']).'${2}', $content);
    }
    if (!empty($_POST['phone'])) {
        $phone = htmlspecialchars($_POST['phone']);
        $content = preg_replace('/(<h4>お問い合わせ<\/h4>\s*<p><a href="tel:)[^"]+("[^>]*>)[^<]+(<\/a>)/s', '${1}'.$phone.'${2}'.$phone.'${3}', $content);
        $content = preg_replace('/<a href="tel:[^"]+"/s', '<a href="tel:'.$phone.'"', $content);
    }
    file_put_contents($file, $content);
    $message = '<div class="success">営業情報を保存しました</div>';
}

// TOPページ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_top'])) {
    $file = '../index.html';
    $content = file_get_contents($file);
    if (!empty($_POST['welcome_text'])) {
        $text = htmlspecialchars($_POST['welcome_text']);
        // 改行を<p>タグで分割
        $paragraphs = array_filter(array_map('trim', explode("\n", $text)));
        $html = '';
        foreach ($paragraphs as $p) {
            if (!empty($p)) {
                $html .= "\n                <p>" . str_replace("\n", "<br>", $p) . "</p>";
            }
        }
        $content = preg_replace('/(<div class="about-text-center">).*?(<\/div>)/s', '${1}' . $html . "\n            " . '${2}', $content);
    }
    if (!empty($_POST['service_title'])) {
        $content = preg_replace('/(<h3 class="service-intro-title">)[^<]+(<\/h3>)/s', '${1}'.htmlspecialchars($_POST['service_title']).'${2}', $content);
    }
    if (!empty($_POST['service_text'])) {
        $content = preg_replace('/(<p class="service-intro-text">)[^<]+(<\/p>)/s', '${1}'.nl2br(htmlspecialchars($_POST['service_text'])).'${2}', $content);
    }
    file_put_contents($file, $content);
    $message = '<div class="success">TOPページを保存しました</div>';
}

// サロン紹介
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_about'])) {
    $file = '../about.html';
    $content = file_get_contents($file);
    if (!empty($_POST['owner_name'])) $content = preg_replace('/(<h3 class="owner-name">)[^<]+(<\/h3>)/s', '${1}'.htmlspecialchars($_POST['owner_name']).'${2}', $content);
    if (!empty($_POST['owner_title'])) $content = preg_replace('/(<p class="owner-title">)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['owner_title']).'${2}', $content);
    if (!empty($_POST['owner_catchphrase'])) $content = preg_replace('/(<p class="owner-catchphrase">)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['owner_catchphrase']).'${2}', $content);
    if (!empty($_POST['owner_message'])) $content = preg_replace('/(<div class="owner-message">\s*<p>)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['owner_message']).'${2}', $content);
    if (!empty($_POST['owner_specialty'])) $content = preg_replace('/(<div class="owner-specialty">\s*<h4>[^<]+<\/h4>\s*<p>)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['owner_specialty']).'${2}', $content);
    if (!empty($_POST['owner_hobby'])) $content = preg_replace('/(<div class="owner-hobby">\s*<h4>[^<]+<\/h4>\s*<p>)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['owner_hobby']).'${2}', $content);
    file_put_contents($file, $content);
    $message = '<div class="success">サロン紹介を保存しました</div>';
}

// サービス説明
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_service'])) {
    $file = '../service.html';
    $content = file_get_contents($file);
    if (!empty($_POST['service_intro'])) $content = preg_replace('/(<p class="intro-text">)[^<]+(<\/p>)/s', '${1}'.nl2br(htmlspecialchars($_POST['service_intro'])).'${2}', $content);
    if (!empty($_POST['cosmelift_lead'])) $content = preg_replace('/(id="cosmelift".*?<p class="service-lead">)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['cosmelift_lead']).'${2}', $content);
    if (!empty($_POST['cosmelift_desc'])) $content = preg_replace('/(id="cosmelift".*?<p class="service-lead">[^<]+<\/p>\s*<p>)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['cosmelift_desc']).'${2}', $content);
    if (!empty($_POST['flatlash_lead'])) $content = preg_replace('/(id="flatlash".*?<p class="service-lead">)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['flatlash_lead']).'${2}', $content);
    if (!empty($_POST['flatlash_desc'])) $content = preg_replace('/(id="flatlash".*?<p class="service-lead">[^<]+<\/p>\s*<p>)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['flatlash_desc']).'${2}', $content);
    if (!empty($_POST['facial_lead'])) $content = preg_replace('/(id="facial-wax".*?<p class="service-lead">)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['facial_lead']).'${2}', $content);
    if (!empty($_POST['facial_desc'])) $content = preg_replace('/(id="facial-wax".*?<p class="service-lead">[^<]+<\/p>\s*<p>)[^<]+(<\/p>)/s', '${1}'.htmlspecialchars($_POST['facial_desc']).'${2}', $content);
    file_put_contents($file, $content);
    $message = '<div class="success">サービス説明を保存しました</div>';
}

// 商品編集
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_products'])) {
    $file = '../products.html';
    $content = file_get_contents($file);

    // ページ説明
    if (!empty($_POST['products_intro'])) {
        $content = preg_replace('/(<p class="page-description">)[^<]+(<\/p>)/s', '${1}'.nl2br(htmlspecialchars($_POST['products_intro'])).'${2}', $content);
    }

    // 各商品を更新
    $products = $_POST['products'] ?? [];
    foreach ($products as $idx => $p) {
        if (!empty($p['name']) && !empty($p['desc']) && !empty($p['price'])) {
            $pattern = '/(<article class="product-card">.*?<h2 class="product-name">)[^<]+(<\/h2>.*?<p class="product-description">)[^<]+(<\/p>.*?<p class="product-price">)[^<]+(<span class="tax">)/s';
            $name = htmlspecialchars($p['name']);
            $desc = htmlspecialchars($p['desc']);
            $price = htmlspecialchars($p['price']);

            // idx番目の商品を更新
            $count = 0;
            $content = preg_replace_callback($pattern, function($m) use ($name, $desc, $price, $idx, &$count) {
                if ($count == $idx) {
                    $count++;
                    return $m[1] . $name . $m[2] . $desc . $m[3] . $price . $m[4];
                }
                $count++;
                return $m[0];
            }, $content);
        }
    }

    file_put_contents($file, $content);
    $message = '<div class="success">商品情報を保存しました</div>';
}

// 商品追加
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['new_product_name'] ?? '');
    $desc = trim($_POST['new_product_desc'] ?? '');
    $price = trim($_POST['new_product_price'] ?? '');

    if ($name && $desc && $price) {
        $file = '../products.html';
        $content = file_get_contents($file);

        // 画像アップロード処理
        $image_filename = 'product-' . date('YmdHis') . '.jpg';
        $image_path = 'images/products/' . $image_filename;

        if (isset($_FILES['new_product_image']) && $_FILES['new_product_image']['error'] === 0) {
            $tmp = $_FILES['new_product_image'];
            $ext = strtolower(pathinfo($tmp['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $image_filename = 'product-' . date('YmdHis') . '.' . $ext;
                $image_path = 'images/products/' . $image_filename;
                move_uploaded_file($tmp['tmp_name'], '../' . $image_path);
            }
        } else {
            // デフォルト画像を使用
            $image_path = 'images/products/default.jpg';
        }

        // 新商品HTML
        $new_product = '
                <!-- ' . htmlspecialchars($name) . ' -->
                <article class="product-card">
                    <div class="product-image">
                        <img src="' . $image_path . '" alt="' . htmlspecialchars($name) . '" loading="lazy">
                    </div>
                    <div class="product-content">
                        <h2 class="product-name">' . htmlspecialchars($name) . '</h2>
                        <p class="product-description">' . htmlspecialchars($desc) . '</p>
                        <p class="product-price">' . htmlspecialchars($price) . '<span class="tax">（税込）</span></p>
                    </div>
                </article>';

        // products-gridの最後に追加
        $content = preg_replace('/(<\/article>\s*)(<\/div>\s*<div class="products-note">)/s', '$1' . $new_product . "\n            $2", $content);

        file_put_contents($file, $content);
        $message = '<div class="success">商品を追加しました</div>';
    } else {
        $message = '<div class="error">商品名、説明、価格は必須です</div>';
    }
}

// 商品削除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $idx = intval($_POST['delete_idx']);
    $file = '../products.html';
    $content = file_get_contents($file);

    // idx番目の商品を削除
    $count = 0;
    $content = preg_replace_callback('/(<!-- [^>]+ -->\s*)?<article class="product-card">.*?<\/article>\s*/s', function($m) use ($idx, &$count) {
        if ($count == $idx) {
            $count++;
            return ''; // 削除
        }
        $count++;
        return $m[0];
    }, $content);

    file_put_contents($file, $content);
    $message = '<div class="success">商品を削除しました</div>';
}

// ====== 抽出関数 ======
function extractPrice($content, $label) {
    if (preg_match('/<span class="price-label">'.preg_quote($label,'/').'.*?<span class="price-value">¥([0-9]+)/s', $content, $m)) return $m[1];
    return '';
}
function extractText($content, $pattern) {
    if (preg_match($pattern, $content, $m)) return trim(strip_tags(str_replace(['<br>','<br />','<br/>'], "\n", $m[1])));
    return '';
}
function getProducts($content) {
    $products = [];
    preg_match_all('/<article class="product-card">.*?<h2 class="product-name">(.*?)<\/h2>.*?<p class="product-description">(.*?)<\/p>.*?<p class="product-price">(.*?)<span/s', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $products[] = [
            'name' => strip_tags(str_replace('<br>', ' ', $m[1])),
            'desc' => strip_tags($m[2]),
            'price' => strip_tags($m[3])
        ];
    }
    return $products;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>サイト編集 | FLEURIR</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Hiragino Sans',sans-serif;background:#f5f3f0}
        .header{background:#9F886E;color:#fff;padding:15px 20px;display:flex;justify-content:space-between;align-items:center}
        .header h1{font-size:20px}
        .nav{background:#fff;padding:15px;border-bottom:1px solid #ddd}
        .nav a{padding:10px 20px;margin-right:10px;background:#f5f3f0;color:#5a4a3a;text-decoration:none;border-radius:5px;display:inline-block;margin-bottom:5px}
        .nav a:hover,.nav a.active{background:#9F886E;color:#fff}
        .tabs{background:#fff;padding:10px 20px;border-bottom:1px solid #eee;overflow-x:auto;white-space:nowrap}
        .tabs a{padding:8px 15px;margin-right:3px;color:#5a4a3a;text-decoration:none;border-radius:5px;display:inline-block;font-size:14px}
        .tabs a:hover{background:#f5f3f0}
        .tabs a.active{background:#9F886E;color:#fff}
        .container{max-width:900px;margin:20px auto;padding:0 20px}
        .card{background:#fff;padding:25px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);margin-bottom:20px}
        .card h2{color:#9F886E;margin-bottom:20px;font-size:18px;border-bottom:2px solid #f5f3f0;padding-bottom:10px}
        .form-group{margin-bottom:20px}
        label{display:block;margin-bottom:8px;font-weight:bold;color:#5a4a3a}
        input[type="text"],textarea{width:100%;padding:12px;border:1px solid #ddd;border-radius:5px;font-size:15px}
        textarea{height:120px;resize:vertical;font-family:inherit}
        .btn{background:#9F886E;color:#fff;padding:15px 40px;border:none;border-radius:5px;font-size:16px;cursor:pointer}
        .btn:hover{background:#8a7560}
        .btn-logout{background:#999;padding:8px 15px;font-size:13px}
        .success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin-bottom:20px}
        .section{background:#fafafa;padding:20px;border-radius:5px;margin-bottom:15px;border-left:4px solid #9F886E}
        .section h3{color:#9F886E;margin-bottom:15px;font-size:16px}
        .price-row{display:flex;gap:15px;margin-bottom:10px;align-items:center}
        .price-row label{margin:0;min-width:150px;font-weight:normal}
        .price-row input{width:120px}
        .help{font-size:13px;color:#888;margin-top:5px}
        .product-item{background:#fafafa;padding:20px;border-radius:5px;margin-bottom:15px;border-left:4px solid #9F886E}
        .product-item h3{color:#9F886E;margin-bottom:15px}
        .product-item .form-group{margin-bottom:15px}
        .product-item input,.product-item textarea{margin-bottom:10px}
        .product-item textarea{height:80px}
    </style>
</head>
<body>
    <div class="header">
        <h1>FLEURIR サイト編集</h1>
        <form method="post" style="display:inline;"><button type="submit" name="logout" class="btn btn-logout">ログアウト</button></form>
    </div>

    <div class="nav">
        <a href="edit.php" class="active">サイト編集</a>
        <a href="images.php">画像管理</a>
        <a href="blog.php">ブログ投稿</a>
        <a href="news.php">お知らせ投稿</a>
        <a href="index.html">マニュアル</a>
    </div>

    <div class="tabs">
        <?php $tab = $_GET['tab'] ?? 'menu'; ?>
        <a href="?tab=menu" <?php echo $tab==='menu'?'class="active"':''; ?>>料金メニュー</a>
        <a href="?tab=info" <?php echo $tab==='info'?'class="active"':''; ?>>営業情報</a>
        <a href="?tab=top" <?php echo $tab==='top'?'class="active"':''; ?>>TOPページ</a>
        <a href="?tab=about" <?php echo $tab==='about'?'class="active"':''; ?>>サロン紹介</a>
        <a href="?tab=service" <?php echo $tab==='service'?'class="active"':''; ?>>サービス</a>
        <a href="?tab=products" <?php echo $tab==='products'?'class="active"':''; ?>>商品</a>
    </div>

    <div class="container">
        <?php echo $message; ?>

        <?php if ($tab === 'menu'):
            $c = file_get_contents('../menu.html');
        ?>
        <form method="post">
            <div class="card">
                <h2>まつ毛エクステ（フラットマットラッシュ）</h2>
                <div class="section">
                    <?php foreach(['80本'=>'eyelash_80','100本'=>'eyelash_100','120本'=>'eyelash_120','140本'=>'eyelash_140','2時間付け放題'=>'eyelash_unlimited'] as $l=>$n): ?>
                    <div class="price-row"><label><?php echo $l; ?></label><input type="text" name="<?php echo $n; ?>" value="<?php echo extractPrice($c,$l); ?>"><span>円</span></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card">
                <h2>コスメリフト</h2>
                <div class="section">
                    <div class="price-row"><label>コスメリフト</label><input type="text" name="cosmelift" value="<?php echo extractPrice($c,'コスメリフト</span>'); ?>"><span>円</span></div>
                    <div class="price-row"><label>コスメリフト下</label><input type="text" name="cosmelift_lower" value="<?php echo extractPrice($c,'コスメリフト下'); ?>"><span>円</span></div>
                </div>
            </div>
            <div class="card">
                <h2>眉WAXスタイリング</h2>
                <div class="section">
                    <div class="price-row"><label>眉WAXスタイリング</label><input type="text" name="eyebrow" value="<?php echo extractPrice($c,'眉WAXスタイリング</span>'); ?>"><span>円</span></div>
                </div>
            </div>
            <div class="card">
                <h2>フェイシャルWAX</h2>
                <div class="section">
                    <div class="price-row"><label>フェイシャルWAX</label><input type="text" name="facial" value="<?php echo extractPrice($c,'フェイシャルWAX</span>'); ?>"><span>円</span></div>
                    <div class="price-row"><label>部分WAX</label><input type="text" name="facial_part" value="<?php echo extractPrice($c,'部分WAX</span>'); ?>"><span>円</span></div>
                    <div class="price-row"><label>うなじ</label><input type="text" name="facial_nape" value="<?php echo extractPrice($c,'うなじ'); ?>"><span>円</span></div>
                </div>
            </div>
            <button type="submit" name="save_menu" class="btn">変更を保存</button>
        </form>

        <?php elseif ($tab === 'info'):
            $c = file_get_contents('../index.html');
            preg_match('/<h4>営業時間<\/h4>\s*<p>([^<]+)<\/p>/s', $c, $h);
            preg_match('/<h4>定休日<\/h4>\s*<p>([^<]+)<\/p>/s', $c, $ho);
            preg_match('/<h4>お問い合わせ<\/h4>\s*<p><a[^>]+>([^<]+)<\/a>/s', $c, $p);
        ?>
        <form method="post">
            <div class="card">
                <h2>営業情報</h2>
                <div class="form-group"><label>営業時間</label><input type="text" name="hours" value="<?php echo htmlspecialchars($h[1]??''); ?>"></div>
                <div class="form-group"><label>定休日</label><input type="text" name="holiday" value="<?php echo htmlspecialchars($ho[1]??''); ?>"></div>
                <div class="form-group"><label>電話番号</label><input type="text" name="phone" value="<?php echo htmlspecialchars($p[1]??''); ?>"><p class="help">予約ボタンの電話番号も変更されます</p></div>
            </div>
            <button type="submit" name="save_info" class="btn">変更を保存</button>
        </form>

        <?php elseif ($tab === 'top'):
            $c = file_get_contents('../index.html');
            preg_match('/<div class="about-text-center">\s*<p>(.*?)<\/p>/s', $c, $w);
            $wt = isset($w[1]) ? strip_tags(str_replace(['<br>','<br />'], "\n", $w[1])) : '';
        ?>
        <form method="post">
            <div class="card">
                <h2>Welcome セクション</h2>
                <div class="form-group"><label>紹介文</label><textarea name="welcome_text"><?php echo htmlspecialchars($wt); ?></textarea><p class="help">改行はそのまま反映されます</p></div>
            </div>
            <div class="card">
                <h2>こだわりの技術 セクション</h2>
                <div class="form-group"><label>タイトル</label><input type="text" name="service_title" value="<?php echo extractText($c,'/<h3 class="service-intro-title">([^<]+)<\/h3>/s'); ?>"></div>
                <div class="form-group"><label>説明文</label><textarea name="service_text"><?php echo extractText($c,'/<p class="service-intro-text">([^<]+)<\/p>/s'); ?></textarea></div>
            </div>
            <button type="submit" name="save_top" class="btn">変更を保存</button>
        </form>

        <?php elseif ($tab === 'about'):
            $c = file_get_contents('../about.html');
        ?>
        <form method="post">
            <div class="card">
                <h2>プロフィール情報</h2>
                <div class="form-group"><label>肩書き</label><input type="text" name="owner_title" value="<?php echo extractText($c,'/<p class="owner-title">([^<]+)<\/p>/s'); ?>"></div>
                <div class="form-group"><label>名前</label><input type="text" name="owner_name" value="<?php echo extractText($c,'/<h3 class="owner-name">([^<]+)<\/h3>/s'); ?>"></div>
                <div class="form-group"><label>キャッチフレーズ</label><input type="text" name="owner_catchphrase" value="<?php echo extractText($c,'/<p class="owner-catchphrase">([^<]+)<\/p>/s'); ?>"></div>
                <div class="form-group"><label>メッセージ</label><textarea name="owner_message"><?php echo extractText($c,'/<div class="owner-message">\s*<p>([^<]+)<\/p>/s'); ?></textarea></div>
                <div class="form-group"><label>得意な技術</label><textarea name="owner_specialty"><?php echo extractText($c,'/<div class="owner-specialty">\s*<h4>[^<]+<\/h4>\s*<p>([^<]+)<\/p>/s'); ?></textarea></div>
                <div class="form-group"><label>趣味・マイブーム</label><textarea name="owner_hobby"><?php echo extractText($c,'/<div class="owner-hobby">\s*<h4>[^<]+<\/h4>\s*<p>([^<]+)<\/p>/s'); ?></textarea></div>
            </div>
            <button type="submit" name="save_about" class="btn">変更を保存</button>
        </form>

        <?php elseif ($tab === 'service'):
            $c = file_get_contents('../service.html');
        ?>
        <form method="post">
            <div class="card">
                <h2>サービス紹介文</h2>
                <div class="form-group"><label>ページ冒頭の説明</label><textarea name="service_intro"><?php echo extractText($c,'/<p class="intro-text">([^<]+)<\/p>/s'); ?></textarea></div>
            </div>
            <div class="card">
                <h2>コスメリフト</h2>
                <div class="form-group"><label>キャッチコピー</label><input type="text" name="cosmelift_lead" value="<?php echo extractText($c,'/id="cosmelift".*?<p class="service-lead">([^<]+)<\/p>/s'); ?>"></div>
                <div class="form-group"><label>説明文</label><textarea name="cosmelift_desc"><?php echo extractText($c,'/id="cosmelift".*?<p class="service-lead">[^<]+<\/p>\s*<p>([^<]+)<\/p>/s'); ?></textarea></div>
            </div>
            <div class="card">
                <h2>フラットマットラッシュ</h2>
                <div class="form-group"><label>キャッチコピー</label><input type="text" name="flatlash_lead" value="<?php echo extractText($c,'/id="flatlash".*?<p class="service-lead">([^<]+)<\/p>/s'); ?>"></div>
                <div class="form-group"><label>説明文</label><textarea name="flatlash_desc"><?php echo extractText($c,'/id="flatlash".*?<p class="service-lead">[^<]+<\/p>\s*<p>([^<]+)<\/p>/s'); ?></textarea></div>
            </div>
            <div class="card">
                <h2>フェイシャルWAX</h2>
                <div class="form-group"><label>キャッチコピー</label><input type="text" name="facial_lead" value="<?php echo extractText($c,'/id="facial-wax".*?<p class="service-lead">([^<]+)<\/p>/s'); ?>"></div>
                <div class="form-group"><label>説明文</label><textarea name="facial_desc"><?php echo extractText($c,'/id="facial-wax".*?<p class="service-lead">[^<]+<\/p>\s*<p>([^<]+)<\/p>/s'); ?></textarea></div>
            </div>
            <button type="submit" name="save_service" class="btn">変更を保存</button>
        </form>

        <?php elseif ($tab === 'products'):
            $c = file_get_contents('../products.html');
            $products = getProducts($c);
        ?>
        <form method="post">
            <div class="card">
                <h2>ページ説明</h2>
                <div class="form-group"><label>冒頭の説明文</label><textarea name="products_intro"><?php echo extractText($c,'/<p class="page-description">([^<]+)<\/p>/s'); ?></textarea></div>
            </div>

            <div class="card">
                <h2>商品一覧（編集）</h2>
                <p class="help" style="margin-bottom:20px;">各商品の情報を編集できます。画像変更は「画像管理」から行ってください。</p>

                <?php foreach ($products as $idx => $p): ?>
                <div class="product-item">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <h3>商品 <?php echo $idx + 1; ?></h3>
                    </div>
                    <div class="form-group">
                        <label>商品名</label>
                        <input type="text" name="products[<?php echo $idx; ?>][name]" value="<?php echo htmlspecialchars($p['name']); ?>">
                    </div>
                    <div class="form-group">
                        <label>説明</label>
                        <textarea name="products[<?php echo $idx; ?>][desc]"><?php echo htmlspecialchars($p['desc']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>価格（税込表記込み）</label>
                        <input type="text" name="products[<?php echo $idx; ?>][price]" value="<?php echo htmlspecialchars($p['price']); ?>" placeholder="¥11,000">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="save_products" class="btn">変更を保存</button>
        </form>

        <form method="post" enctype="multipart/form-data" style="margin-top:30px;">
            <div class="card">
                <h2>新規商品を追加</h2>
                <div class="form-group">
                    <label>商品名 *</label>
                    <input type="text" name="new_product_name" placeholder="例：新商品名">
                </div>
                <div class="form-group">
                    <label>説明 *</label>
                    <textarea name="new_product_desc" placeholder="商品の説明を入力"></textarea>
                </div>
                <div class="form-group">
                    <label>価格 *</label>
                    <input type="text" name="new_product_price" placeholder="¥11,000">
                </div>
                <div class="form-group">
                    <label>商品画像</label>
                    <input type="file" name="new_product_image" accept="image/*">
                    <p class="help">JPG, PNG, GIF, WEBP対応。未選択の場合はデフォルト画像が使用されます。</p>
                </div>
                <button type="submit" name="add_product" class="btn">商品を追加</button>
            </div>
        </form>

        <div class="card" style="margin-top:30px;">
            <h2>商品を削除</h2>
            <p class="help" style="margin-bottom:15px;">削除したい商品を選択してください。</p>
            <form method="post">
                <div class="form-group">
                    <select name="delete_idx" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:5px;">
                        <?php foreach ($products as $idx => $p): ?>
                        <option value="<?php echo $idx; ?>">商品<?php echo $idx + 1; ?>: <?php echo htmlspecialchars($p['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="delete_product" class="btn" style="background:#c00;" onclick="return confirm('本当にこの商品を削除しますか？')">選択した商品を削除</button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
