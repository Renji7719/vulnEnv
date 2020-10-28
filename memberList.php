<?php
//MySQL周りを管理するClass
//DBクラス
class db_manager {
  private $dbh;
  public function __construct() {
      // サーバー設定
      $login_id = 'ーーーーー';
      $pass = 'ーーーーーーー';
      $dbname = 'vulnEnv';
      $host = 'localhost';
      $this->dbh = new mysqli($host, $login_id, $pass, $dbname);
      if ($this->dbh->connect_error) {
        echo $this->dbh->connect_error;
        exit();
      } else {
          $this->dbh->set_charset("utf8");
      }
  }
  //検索
  public function search($table,$column,$query){
    try{
        $dbh = $this->dbh;
        $searchSQL = "SELECT * FROM ".$table." where ".$column." LIKE '%".$query."%'";
        $rows = array();
        if ($result = $dbh->query($searchSQL)) {
          //連想配列で取得
          while($row = $result->fetch_array(MYSQLI_ASSOC)){
            $rows[] = $row;
          }
          // 結果セットを閉じる
          $result->close();
      }
        return $rows;
    }catch (Exception $e){
        var_dump($e);
        return $e->getMessage();
    }
  }
  //データを削除する処理
  public function delete($table,$idArray){
     try{
      $dbh = $this->dbh;
      //配列で受け取ったidをforeachで繰り返してdelete処理
      foreach($idArray as $id){
        $deleteSQL = "DELETE FROM ".$table." WHERE id = ".$id;
        $dbh->query($deleteSQL);
      }
    }catch(Exception $e){
      var_dump($e);
      return $e->getMessage();
    }
  }
  //データを挿入する処理
  public function insert($table,$name,$year){
    try{
      $dbh = $this->dbh;
      //selectで表を全て表示してから、結果の行数をintに変換して1を足してる。
      $result = $dbh->query("SELECT * FROM memberList");
      $maxCount = (int)$result->num_rows + 1;
      $insertSQL = "INSERT INTO ".$table." values(".$maxCount.",'".$name."',".$year.")";
      $dbh->query($insertSQL);
    }catch(Exception $e){
      var_dump($e);
      return $e->getMessage();
    }
  }
  //データベースを元に戻す処理
  public function remake($table){
    try{
        $dbh = $this->dbh;
        $id = array(1,2,3,4,5,6,7,8,9,10,11);
        $name = array(ーーーーーーーーーーーーー);
        $year = array(4,4,4,4,4,3,3,3,2,2,2);
        $deleteSQL = "truncate table ".$table;
        $dbh->query($deleteSQL);
        for($i = 0 ; $i < count($id); $i++){ 
          $insertSQL = "insert into ".$table." values (".$id[$i].",'".$name[$i]."',".$year[$i].")";
          $dbh->query($insertSQL);
        }
    }catch (Exception $e){
        var_dump($e);
    }
  }
  public function __destruct(){
      // DB接続を閉じる
      $dbh = $this->dbh;
      $dbh->close();
  }
}

//なんの処理か判定するために、buttonパラメータに格納されているものを$button変数に格納する。
if(isset($_GET["button"])){
  $button = $_GET["button"];
}else{
  $button = "button is not pushing!";
}
// ここにDB処理いろいろ書く
$sql = new db_manager();
//GETパラメータにsearch、columnがあり、searchが空欄でなければ検索処理。
//GETパラメータに何もなければ、全て表示
if(isset($_GET["searchQuery"])&&isset($_GET["column"])&&$_GET['searchQuery']!=""&&$button=="searchMemberList"){
  //検索処理
  $searchQuery = $_GET["searchQuery"];
  $searchColumn = $_GET["column"];
  if($searchColumn=="id"|$searchColumn=="year"){
    $searchQuery = (int)$searchQuery;
  }
  $rows = $sql->search("memberList",$searchColumn,$searchQuery);
}elseif(isset($_GET["tableID"])&&$button=="deleteMemberList"){
  //削除ボタンが押されたら、パラメータtableIDに格納されているidと等しいカラムを削除する。
  var_dump(gettype($_GET["tableID"]));
  $sql->delete("memberList",$_GET["tableID"]);
  $rows = $sql->search("memberList","id","");
}elseif(isset($_GET['name'])&&isset($_GET['year'])&&$button=="insertMemberList"){
  //挿入ボタンが押されたら
  $sql->insert("memberList",$_GET['name'],$_GET['year']);
  $rows = $sql->search("memberList","id","");
}elseif($button=="remakeMemberList"){
  //データベースを元に戻すボタンが押されたら
  //データをリフレッシュしてから、全表示
  $sql->remake("memberList");
  $rows = $sql->search("memberList","id","");
}else{
  // ページをロードした時の処理
  //searchメソッドを応用して、全てを表示
  $rows = $sql->search("memberList","id","");
}
?>

<!doctype html>
<html lang="jp">
  <head>
   <!-- Required meta tags -->
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="./css/bootstrap.min.css">
   <title>メンバリスト</title>
  </head>
  <body>
    <script>
      //値が整数かどうかをチェックする。整数出なかったら処理を中断させる。
      //処理を中断させるには、formタグにonsubmit="return checkNum()"と呼び出す必要がある。
      function checkNum() {
        var year = parseInt(document.getElementById('year').value);
        if(Number.isInteger(year)){
          return true;
        }else{
          alert('年には整数値を入れてください')
          return false;
        }
      }

    </script>
   <h1 class="display-1">メンバリスト</h1>
   <form action = memberList.php method = “get”>
   <h2>検索</h2>
   <select name=column>
     <option>id</option>
     <option>name</option>
     <option>year</option>
   </select><br>
   <input type="text" name="searchQuery" size="30" maxlength="20" id="searchQuery">　　
   <button type="submit" class="btn btn-outline-dark" name="button" value="searchMemberList">検索</button><br>
   </form>
   <hr>
   <form action = memberList.php method = “get”>
   <table class="table table-bordered table-hover">
     <thead class="thead-dark">
      <tr>
       <th scope="col">id</th>
       <th scope="col">Name</th>
       <th scope="col">Year</th>
      </tr>
     </thead>
     <tbody>
     <?php 
      foreach($rows as $row){
    ?> 
    <tr> 
	  <td><input type="checkbox" name="tableID[]" value=<?php echo $row['id']; ?>>　<?php echo $row['id']; ?></td> 
    <td><?php echo htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8'); ?></td> 
    <td><?php echo htmlspecialchars($row['year'],ENT_QUOTES,'UTF-8'); ?></td> 
    </tr> 
    <?php 
    } 
    ?>
     </tbody>
   </table>
   <h2>メンバ削除</h2>
   削除したいメンバにチェックを入れてから削除ボタンを押してください。　　　　　　　　
   <button type="submit" class="btn btn-outline-danger" name="button" value="deleteMemberList">削除</button><br><br>
   </form>　
   <h2>メンバ追加</h2>
   <form action = memberList.php method = “get” onsubmit="return checkNum()">
   名前と社会人歴を入力してから追加ボタンを押してください。<br>
   名前：<input type="text" name="name" size="30" maxlength="20" id="name">　　年：<input type="text" name="year" size="30" maxlength="20" id="year">　　
   <button type="submit" class="btn btn-outline-info" name="button" value="insertMemberList">追加</button><br>
   </form>

   <br>
   <br>
   <br>
   <hr>
   <form action = memberList.php method = “get”>
    <button type="submit" class="btn btn-outline-success" name="button" value="remakeMemberList">データベースを元に戻す</button>
   </form>
   <br>
   <br>
   <!-- Optional JavaScript -->
   <!-- jQuery first, then Popper.js, then Bootstrap JS -->
   <script src="./js/jquery-3.4.1.min.js"></script>
   <script src="./js/popper.min.js"></script>
   <script src="./js/bootstrap.min.js"></script>

  </body>
</html>
