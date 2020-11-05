<?php

    // セッション開始
    session_start();
    
    // データベース接続情報
    $dsn = 'mysql:host=localhost;dbname=bbs';
    $username = 'root';
    $password = '';
    
    // 接続オプション
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // 失敗したら例外を投げる
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   //デフォルトのフェッチモードは連想配列
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',   //MySQL サーバーへの接続時に実行するコマンド
    ); 
    
    // 注目している投稿のID
    $message_id = "";
    
    // 注目している投稿
    $message = "";
    
    // 削除する画像ファイル名
    $del_image = "";

    // フラッシュメッセージを保存する変数
    $flash_message = "";
    
    // 画像をアップロードするフォルダ名
    $image_dir = "upload/";
    
    // 注目している投稿のIDを取得
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        $message_id = $_GET['id'];
    }else{
        $message_id = $_POST['id'];
    }
    
    // 例外処理
    try {
        
        // データベースに接続
        $pdo = new PDO($dsn, $username, $password, $options);
  
        // SELECT文実行準備
        $stmt = $pdo->prepare('SELECT * FROM messages where id=:id');
        // バインド処理
        $stmt->bindParam(':id', $message_id, PDO::PARAM_INT);
        // SELECT文実行
        $stmt->execute();
        
        // 注目している投稿の取得
        $message = $stmt->fetch();
        
        // 削除ファイル名取得
        $del_image = $message['image'];
        
    } catch (PDOException $e) {
        echo 'PDO exception: ' . $e->getMessage();
        exit;
    }
        
    // 編集、もしくは削除ボタンが押された時
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        
        // 更新ボタンが押されたならば    
        if($_POST['kind_method'] === 'update'){
            
            // 入力した値を取得
            $name = $_POST['name'];
            $title = $_POST['title'];
            $body = $_POST['body'];
            
            // ファイルが選択されていれば
            if (!empty($_FILES['image']['name'])) {

                // uploadフォルダから画像を削除
                unlink($image_dir . $del_image);
            
                // 新しい画像ファイル名を適当の作成
                $image = uniqid(mt_rand(), true); //ファイル名をユニーク化
                $image .= '.' . substr(strrchr($_FILES['image']['name'], '.'), 1);//アップロードされたファイルの拡張子を取得
                $file = $image_dir . $image;
            
                // 新しい画像をuploadフォルダに保存
                move_uploaded_file($_FILES['image']['tmp_name'], $file);
    
                // UPDATE文の準備
                $stmt = $pdo->prepare('UPDATE messages SET name=:name, title=:title, body=:body, image=:image WHERE id=:id');
                // バインド処理
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                $stmt->bindParam(':body', $body, PDO::PARAM_STR);
                $stmt->bindParam(':image', $image, PDO::PARAM_STR);
                $stmt->bindParam(':id', $message_id, PDO::PARAM_INT);
          
                // UPDATE文の実行
                $stmt->execute();
            
                // フラッシュメッセージの準備とセッションへの保存        
                $flash_message = "投稿がすべて更新されました。";
                $_SESSION['flash_message'] = $flash_message;
                
            }
            
        // 削除ボタンが押されたならば    
        }else if($_POST['kind_method'] === 'delete'){
                
            // DELETE文の準備
            $stmt = $pdo->prepare('DELETE FROM messages WHERE id=:id');
            // バインド処理
            $stmt->bindParam(':id', $message_id, PDO::PARAM_INT);
            // DELETE文の実行
            $stmt->execute();
            // 画像をuploadフォルダから削除
            unlink($image_dir . $del_image);
            
            // フラッシュメッセージの準備とセッションへの保存   
            $flash_message = "投稿が削除されました。";
            $_SESSION['flash_message'] = $flash_message;
        }    
            
        // 画面遷移
        header('Location: index.php');
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

        <title>投稿詳細</title>
        <style>
            h2{
                color: red;
                background-color: pink;
            }
            img{
                width: 60%;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row mt-2">
                <h1 class="text-center col-sm-12">id: <?php print $message_id; ?> の投稿詳細</h1>
            </div>
            <div class="row mt-2">
                <form class="col-sm-12" action="show.php" method="POST" enctype="multipart/form-data">
               
                    <!-- 1行 -->
                    <div class="form-group row">
                        <label class="col-2 col-form-label">名前</label>
                        <div class="col-10">
                            <input type="text" class="form-control" name="name" required value="<?php print $message['name']; ?>">
                        </div>
                    </div>
                
                    <!-- 1行 -->
                    <div class="form-group row">
                        <label class="col-2 col-form-label">タイトル</label>
                        <div class="col-10">
                            <input type="text" class="form-control" name="title" required value="<?php print $message['title']; ?>";>
                        </div>
                    </div>
                    
                    <!-- 1行 -->
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">内容</label>
                        <div class="col-10">
                            <input type="text" class="form-control" name="body" required value="<?php print $message['body']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-2 col-form-label">現在の画像</label>
                        <div class="col-10">
                            <img src="<?php if(file_exists($image_dir . $message['image'])){ print $image_dir . $message['image']; }else{ print 'no-image.png';} ?>" alt="表示する画像がありません。">
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
                    
                    <div class="row">
                        <input type="hidden" name="id" value="<?php print $message['id']; ?>">
                    </div>

                    <!-- 1行 -->
                    <div class="form-group row">
                        <div class="offset-sm-2 col-sm-1">
                            <button type="submit" name="kind_method" value="update" class="btn btn-primary">更新</button>
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" name="kind_method" value="delete" class="btn btn-danger" onclick="return confirm('投稿を削除します。よろしいですか？')">削除</button>
                        </div>
                    </div>
                </form>
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