<?php

    // セッションスタート
    session_start();
    
    // データベース接続情報
    $dsn = 'mysql:host=localhost;dbname=bbs';
    $username = 'root';
    $password = '';
   
    // 画像をアップロードするフォルダ名
    $image_dir = "upload/";
    
    // 投稿ボタンが押された時
    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        //  入力情報を取得
        $name = $_POST['name'];
        $title = $_POST['title'];
        $body = $_POST['body'];
        
        // 例外処理
        try {
    
           // 接続オプション
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // 失敗したら例外を投げる
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   //デフォルトのフェッチモードは連想配列
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',   //MySQL サーバーへの接続時に実行するコマンド
            ); 
            
            // データベースに接続
            $pdo = new PDO($dsn, $username, $password, $options);
   
            //ファイルが選択されていれば
            if (!empty($_FILES['image']['name'])) {
            
                // アップロードする画像ファイル名を適当に決める
                $image = uniqid(mt_rand(), true); //ファイル名をユニーク化
                $image .= '.' . substr(strrchr($_FILES['image']['name'], '.'), 1);//アップロードされたファイルの拡張子を取得
                $file = $image_dir . $image;
            
                // uploadフォルダに画像を保存
                move_uploaded_file($_FILES['image']['tmp_name'], $file);
                
                // INSERT文を実行する準備
                $stmt = $pdo -> prepare("INSERT INTO messages (name, title, body, image) VALUES (:name, :title, :body, :image)");
                
                // バインド処理
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                $stmt->bindParam(':body', $body, PDO::PARAM_STR);
                $stmt->bindParam(':image', $image, PDO::PARAM_STR);
                
                // INSERT実行
                $stmt->execute();
                
                // フラッシュメッセージの作成と、セッションへの登録
                $flash_message = "投稿が成功しました。";
                $_SESSION['flash_message'] = $flash_message;
                
                // 画面遷移
                header('Location: index.php');
            
            }
            
        } catch (PDOException $e) {
            echo 'PDO exception: ' . $e->getMessage();
            exit;
        }
    }

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <link rel="shortcut icon" href="favicon.ico">

        <title>新規投稿</title>
        <style>
            h2{
                color: red;
                background-color: pink;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row mt-2">
                <h1 class="text-center col-sm-12">新規投稿</h1>
            </div>
            <div class="row mt-2">
                <h2 class="text-center col-sm-12"><?php print $flash_message; ?></h1>
            </div>
            <div class="row mt-2">
                <form class="col-sm-12" action="new.php" method="POST" enctype="multipart/form-data">
                    <!-- 1行 -->
                    <div class="form-group row">
                        <label class="col-2 col-form-label">名前</label>
                        <div class="col-10">
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                
                    <!-- 1行 -->
                    <div class="form-group row">
                        <label class="col-2 col-form-label">タイトル</label>
                        <div class="col-10">
                            <input type="text" class="form-control" name="title" required>
                        </div>
                    </div>
                    
                    <!-- 1行 -->
                    <div class="form-group row">
                        <label class="col-2 col-form-label">内容</label>
                        <div class="col-10">
                            <input type="text" class="form-control" name="body" required>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-2 col-form-label">画像アップロード</label>
                        <div class="col-3">
                            <input type="file" name="image" accept='image/*' onchange="previewImage(this);"　class="" required　>
                        </div>
                        <div class="col-7">
                            <img id="preview" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" style="max-width:200px;">
                        </div>
                    </div>
                    
                    <!-- 1行 -->
                    <div class="form-group row">
                        <div class="offset-2 col-10">
                            <button type="submit" class="btn btn-primary">投稿</button>
                        </div>
                    </div>
                </form>
            </div>
             <div class="row mt-5">
                <a href="index.php" class="btn btn-primary">投稿一覧</a>
            </div>
        </div>
        

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS, then Font Awesome -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
        <script defer src="https://use.fontawesome.com/releases/v5.7.2/js/all.js"></script>
        <script>
            function previewImage(obj)
            {
            	var fileReader = new FileReader();
            	fileReader.onload = (function() {
            		document.getElementById('preview').src = fileReader.result;
            	});
            	fileReader.readAsDataURL(obj.files[0]);
            }
        </script>
    </body>
</html>