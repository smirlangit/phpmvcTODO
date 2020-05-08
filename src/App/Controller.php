<?php
namespace App;


class Controller {

    protected $model;
    protected $view;
    protected $hashCookieName = 'hash';
    protected $sortCookieName = 'sortingby';
    protected $sortDirCookieName = 'sortdirect';
    
    public function __construct() {
        $this->model = new Model();
        $this->view = new View();
        
    }
    
    public function addTask($name, $mail, $desc) {
        
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->showMsg("invalid email format");
            header('Location: /');
            return false;
          }
          
        $name = strip_tags($name);
        $mail = strip_tags($mail);
        $desc = strip_tags($desc);
          
        $this->model->addTask($name, $mail, $desc);
        $this->setTasksSort("id");
        $this->showMsg("task added!");
        header('Location: /');
        
    }
    
    protected function showMsg($msg) {
        setcookie("msg", $msg, time()+1);
    }
    
    public function editTask($id,  $desc, $page=null) {
        
        if($this->authCheck($this->model::ROLE_ADMIN) ==  false){
            header('Location: /');  
            return false;
        }
        
        $user = $this->getCurrentUser();
           
        $res = $this->model->taskEdit($id, $desc, $user['login']);
        
        if($page != null){
            $page = "?page=$page";
        }
        header('Location: /'.$page);
        
    }

    public function setTasksSort($sortingBy) {
        setcookie($this->sortCookieName, $sortingBy);
        
        //направление сортировки (toggle). Если тип сортировки такой же, значит меняем направление сортировки
        $sortDir = '';
        if($sortingBy == $_COOKIE[$this->sortCookieName]){
          $sortDir = $_COOKIE[$this->sortDirCookieName] == 'asc' ? 'desc':'asc';             
            
        } 
        setcookie($this->sortDirCookieName, $sortDir);
                
        header('Location: /');
        
    }


    public function viewTasksPage($page=0){
        
        $sortingBy = $_COOKIE[$this->sortCookieName];
        $sortDirection = $_COOKIE[$this->sortDirCookieName]=='asc' ? 'ASC' : 'DESC';
        
        //валидация сортировки
        $validSortFileds = ["user_name", "email", "status", "id"];
        if(in_array($sortingBy, $validSortFileds) == false){
            $sortingBy = "id";
        }
        
        if(is_int($page) == false && $page < 0){
            $page = 0;
        }
        
        //получаем данные из модели
        $tasks = $this->model->getTasks($page, $sortingBy, $sortDirection);
        
        //проверка прав доступа
        $isAdmin = false;
        if(isset($_COOKIE[$this->hashCookieName])){
            $isAdmin = $this->model->getAuthByHash($_COOKIE[$this->hashCookieName]);
            
        }
        
        //количество страниц
        $pageCount = ceil(( $this->model->tasksCount()) /  $this->model->taskPerPage);
        
        //если стоит текущая страница указанная в запросе
        $currpage = isset($_GET['page']) ? $_GET['page'] : '';

        //отдаем данные на показ через view
        $this->view->showPage('tasks', ['tasks'=>$tasks, 'isadmin'=>$isAdmin, 'pages'=>$pageCount, 'currpage'=>$currpage]);
        
        $this->model->closeConnect();
        
    }
    
    public function taskComplete($id) {
        if($this->authCheck($this->model::ROLE_ADMIN) ==  false && is_int($id) == false && $id < 0){
            header('Location: /');  
            return false;
        }
        
        
        $this->model->taskComplete($id);

    }
    
    public function login($user, $password) {        
        //поиск пользователя по логину и паролю
        $user = $this->model->getUser($user, $password);       
        
        //если найден, генерируем для него новый хэш
        if($user){
            $hash = $this->model->hashGenerate($user["id"]);
            setcookie($this->hashCookieName, $hash);
        } else {
            $this->showMsg('incorrect username or password');
        }
        header('Location: /');        
        
    }
    
    public function logout() {    
        //получаем пользователя по хэшу
        $hash = $_COOKIE[$this->hashCookieName];
        $user = $this->model->getUserByHash($hash);
        
        //генерируем новый хеш, для обнуления текущего
        $this->model->hashGenerate($user['id']);
        
        //удаляем текущий куки из браузера
        setcookie($this->hashCookieName, '');
        
        header('Location: /');        
        
    }
    

    protected function authCheck($role) {
        $checked = false;
        //получаем роль пользователя по хэшу
        $hash = $_COOKIE[$this->hashCookieName];
        $user = $this->model->getUserByHash($hash);
        if($user['role'] == $role){
            $checked = true;
        } else {
            $checked = false;
        }
        
        return $checked;
    }
    
    protected function getCurrentUser(){
        $hash = $_COOKIE[$this->hashCookieName];
        $user = $this->model->getUserByHash($hash);
        return $user;
    }
    
}
