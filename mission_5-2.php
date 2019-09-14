
<?php
//MySQL連携
$dsn = 'データベース名';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user,$password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));


//table作成
$sql = "CREATE TABLE IF NOT EXISTS post_box"."("
."id INT AUTO_INCREMENT PRIMARY KEY,"
."name char(32),"
."comment TEXT,"
."password char(32),"
."date TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"
.");";
$stmt = $pdo->query($sql);



?>
<?php 

    $time=date("Y/m/d H:i:s");
    $filename = "mission_3-5.txt";
    $edit_name=null;
    $edit_comment=null;
    $edit_number=null;
    $edit_password=null;
    $null=null;
    
    //編集ボタンを押したとき
if(isset($_POST["edit_button"])){
    $edit_number=$_POST["edit"];
        if(empty($edit_number)){
                echo "編集する内容がありません";
        }else{
            $sql = 'SELECT * FROM post_box';
            $stmt = $pdo->query($sql);
            $now_Datas = $stmt->fetchAll();
            if (empty($now_Datas)){
                echo "掲示板に投稿がありません";
            }else{
                $now_count=count($now_Datas);
                if($now_count>=$edit_number){
                    $sql = "SELECT * FROM post_box WHERE id = $edit_number";
                    $stmt = $pdo -> query($sql);
                    $result = $stmt -> fetch();
                    $set_password = $result['password'];
                    if ($_POST["edit_password"]==$set_password){
                        $edit_name = $result['name'];
                        $edit_comment = $result['comment'];
                        $edit_password = $result['password'];
                    }else{
                        echo "パスワードが違います";
                    }
                        
                }else{
                    echo "{$edit_number}の投稿番号はありません";
                }
                
            }
        }       
    
}
 
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>mission_5</title>
    </head>
    <body>
        <form action="mission_5-1.php" method="post">
            <p>名前</p>
            <input type="text" name="name" value="<?php echo $edit_name ?>">
            <p>コメント</p>
            <textarea name="comment" ><?php echo $edit_comment ?></textarea>
            <br>
            <input name="now_edit_number" value="<?php echo $edit_number ?>" type="hidden">
            <p>パスワード設定</p>
            <input type="password" name="password" value="<?php echo $edit_password ?>">
            <input type="submit" name="post_button" value="送信">
            <hr>
            <p>削除番号</p>
            <input type="text" name="delete_number">
            <p>パスワード</p>
            <input type="password" name="delete_password">
            <input type="submit" name="delete_button" value="削除">
            <hr>
            <p>編集対象番号</p>
            <input type="text" name="edit">
            <p>パスワード</p>
            <input type="password" name="edit_password">
            <input type="submit" name="edit_button"  value="編集">
            <hr>
        </form>
        <p>掲示板</p>
    </body>
</html>

<?php
    
    
    //ファイル読み込み
    $sql = 'SELECT * FROM post_box';
    $stmt = $pdo->query($sql);
    $now_Datas = $stmt->fetchAll();
    if(empty($now_Datas)){ 
        $now_count=0;          
    }else{
        $now_count=count($now_Datas);
    }

    //削除ボタンを押したとき

    if(isset($_POST["delete_button"])){
        //パスワード確認
       
            $delete_number=$_POST["delete_number"];
            if ($delete_number<=$now_count){
                $sql = "SELECT * FROM post_box WHERE id = $delete_number";
                $stmt = $pdo->query($sql);
                $result = $stmt -> fetch();
                $set_password=$result['password'];
                if($_POST["delete_password"]==$set_password){
                    $sql = "delete from post_box where id =:id";
                    $stmt = $pdo -> prepare($sql);
                    $stmt->bindParam(':id',$delete_number,PDO::PARAM_INT);
                    $stmt->execute();
                    $sql = "SELECT * FROM post_box";
                    $stmt = $pdo -> query($sql);
                    $results = $stmt->fetchAll();
                    $sql = "alter table post_box drop column id"; // idのカラム削除
                    $stmt = $pdo -> prepare($sql);
                    $stmt->execute();
                    $sql = "alter table post_box add id int primary key not null auto_increment first";  //　idのカラム作成
                    $stmt = $pdo ->prepare($sql);
                    $stmt->execute();    
                    $sql = "SELECT * FROM post_box";
                    $stmt = $pdo -> query($sql);
                    $results = $stmt->fetchAll();
                    foreach ($results as $row){
                        echo $row['id'].'.';
                        echo $row['name'].'.';
                        echo $row['comment'].'.';
                        echo $row['date'].'.';
                        echo "<hr>";
                    }
                   
                   
                }else{
                    echo "パスワードが違います";
                }
            }else{
                echo "{$delete_number}の投稿番号はありません";
            }
    }
    
    //送信ボタン押したとき

    if(isset($_POST["post_button"])){
       //投稿モード
        if (empty($_POST["now_edit_number"])){
                
            $name=$_POST["name"];
            $comment=$_POST["comment"];
            $password=$_POST["password"];
            if (empty($name)){
                echo "名前を入れてください";
            }else{
                if(empty($comment)){
                    echo "コメントを入れてください";
                }else{
                    if(empty($password)){
                        echo "パスワードを入れてください";
                    }else{
                        $new_count=$now_count+1;
                        $sql = $pdo->prepare("INSERT INTO post_box (name,comment,password) VALUES (:name, :comment,:password)");
                        $sql->bindParam(':name',$name,PDO::PARAM_STR);
                        $sql->bindParam(':comment',$comment,PDO::PARAM_STR);
                        $sql->bindParam(':password',$password,PDO::PARAM_STR);
                        $sql->execute();
                        $sql = 'SELECT * FROM post_box';
                        $stmt = $pdo->query($sql);
                        $results = $stmt->fetchAll();
                        foreach ($results as $row ){
                            echo $row['id'].'.';
                            echo $row['name'].'.';
                            echo $row['comment'].'.';
                            echo $row['date'].'.';
                            echo "<hr>";
                        }
                        
                    }
                }       
            }
            
         //編集モード       
        }else{  
            $new_edit_name=$_POST["name"];
            $new_edit_comment=$_POST["comment"];
            $new_edit_password=$_POST["password"];
            $edit_number=$_POST["now_edit_number"];
            
            $sql = "update post_box set name=:name,comment=:comment,password=:password where id=:id";
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindParam(':name',$new_edit_name,PDO::PARAM_STR);
            $stmt -> bindParam(':comment',$new_edit_comment,PDO::PARAM_STR);
            $stmt -> bindParam(':password',$new_edit_password,PDO::PARAM_STR);
            $stmt -> bindParam(':id',$edit_number,PDO::PARAM_INT);
            $stmt -> execute();

            $sql = "SELECT * FROM post_box";
            $stmt = $pdo -> query($sql);
            $results = $stmt -> fetchAll();
            foreach ($results as $row){
                echo $row['id'].'.';
                echo $row['name'].'.';
                echo $row['comment'].'.';
                echo $row['date'].'.';
                echo "<hr>";
            }
        }                 
    }               
?>