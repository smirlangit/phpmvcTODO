<?php

namespace App;
use PDO;

//для упрощения кода данная модель совмещает в себе работу с данными и подключением к базе данных. Однако эти 2 сущности нужно разделять
class Model {

    protected $db;
    protected $dbName = '';
    public $taskPerPage = 3;

    public const ROLE_ADMIN = 'admin';

    public function __construct() {
        $this->db = $this->connect();
    }

    //добавляем задачу
    public function addTask($name, $mail, $desc){
        //универсальный форматы кодировки
        $name = base64_encode($name);
        $mail = base64_encode($mail);
        $desc = base64_encode($desc);


        $res= $this->db->query("INSERT INTO $this->dbName.tasks (user_name, email, description) VALUES ('$name', '$mail', '$desc')");


        return $res;

    }

    //получаем задачи
    public function getTasks($page = 0, $sortingBy=null, $sortDirection=null){

        //способ сортировки
        $orderBy = 'ORDER BY id';
        if($sortingBy != null){
            $orderBy = 'ORDER BY '.$sortingBy;
        }


        $direction = 'ASC';
        if($sortDirection != null){
            $direction = $sortDirection;
        }


        //очистка и валидация параметра
        $page = $this->clear($page);
        if($page < 0) {$page = 0;}
        if(is_numeric($page) == false){$page = 0;}

        $startpage = $page * $this->taskPerPage;

        $data = $this->db->query("SELECT * FROM $this->dbName.tasks $orderBy $direction limit $startpage, $this->taskPerPage ");

        $data = $data->fetchAll();


        return $data;

    }

    //общее количество задач в базе, нужно для пагинации
    public function tasksCount(){
        $data = $this->db->query("SELECT count(id) as count FROM $this->dbName.tasks");
        $data = $data->fetch();

        return $data['count'];
    }

     //редактировани задачи
    public function taskEdit($id, $desc, $userLogin = '') {

        $editedBy = '';
        if($userLogin != ''){
            $editedBy = "editby = '$userLogin' ";
        }

        $desc = base64_encode($desc);
        $res = $this->db->query("UPDATE $this->dbName.tasks SET description = '$desc', $editedBy  WHERE id=$id ");
    }


    //завершение задачи
    public function taskComplete($id) {
         $res = $this->db->query("UPDATE $this->dbName.tasks SET status = 'done' WHERE id=$id ");
    }

    //получаем польз из базы
    public function getUser($login, $pass){
        $data = $this->db->query("SELECT * FROM $this->dbName.users WHERE login='$login' AND pass='$pass' limit 1");
        $data = $data->fetchAll();

        return $data;
    }

    //права пользователя по хешу. Если хеш от клиента равен хешу в базе, то значит залогинен
    public function getAuthByHash($hash) {
        $data = $this->db->query("SELECT * FROM $this->dbName.users WHERE hash='$hash' limit 1");
        $data = $data->fetchAll();

        $auth = false;
        $userHash = $data[0]["hash"];
        if($userHash == $hash){
            $auth =true;
        }
        return $auth;

    }

    public function getUserByHash($hash) {
        $data = $this->db->query("SELECT * FROM $this->dbName.users WHERE hash='$hash' limit 1");
        $user = $data->fetch();

        return $user;
    }


    //генерируем новый хеш для пользователя
    public function hashGenerate($userID){
        $hash = hash("md5", time().openssl_random_pseudo_bytes(32));
        $res= $this->db->query("UPDATE $this->dbName.users SET hash = '$hash'");

        return $hash;
    }


    protected function connect() {
        //параметры подлкючения и создание коннекта должны храниться во внешнем файле, но для минимизации кода они вводятся здесь

        $host = '172.21.0.2';
        $db   = 'homestead';
        $user = 'homestead';
        $pass = 'secret';
        $charset = 'UTF8';

        $this->dbName = $db;

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset;";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,

        ];

        return new PDO($dsn, $user, $pass, $opt);

    }

    public function closeConnect() {
        $this->db = null;
    }

    //выносим очистку запросов отдельно, на тот случай если она будет изменяться
    protected function clear($param) {
        $param = str_replace('\'', '', $param);
        $param = str_replace('\"', '', $param);
        return $param;
    }

}
