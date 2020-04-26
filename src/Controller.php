<?php


class Controller {

    protected $model;
    protected $view;
    protected $hashCookieName = 'hash';
    protected $sortCookieName = 'sortingby';
    protected $sortDirCookieName = 'sortdirect';
    
    public function __construct() {
        
        //классы создаются отдельно от места их использования, для того чтобы использующие их методы, не зависили от метода создания объектов
        $this->model = new Model();
        //нужно сделать объект view как singleton, так как он нужен в едином экземпляре, но для упрощения кода, объект создается как обычно
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
          
        $res = $this->model->addTask($name, $mail, $desc);
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
    
    //тип сортировки в куки
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


    //страницы с пагинацией
    public function viewTasksPage($page=0){
        
        $sortingBy = $_COOKIE[$this->sortCookieName];
        $sortDirection = $_COOKIE[$this->sortDirCookieName]=='asc' ? 'ASC' : 'DESC';
        
        //получаем данные из модели
        $tasks = $this->model->getTasks($page, $sortingBy, $sortDirection);
        
        //проверка прав доступа
        $isAdmin = false;
        if(isset($_COOKIE[$this->hashCookieName])){
            $isAdmin = $this->model->getAuthByHash($_COOKIE[$this->hashCookieName]);
            
        }
        
        //количество страниц
        $pageCount = ceil(( $this->model->tasksCount()) /  $this->model->taskPerPage);
        

        //отдаем данные на показ через view. Так как нужно максимально упростить код, для каждого рендера делается свой метод, вместо универсального
        $this->view->showPage("tasks", ["tasks"=>$tasks, "isadmin"=>$isAdmin, "pages"=>$pageCount]);
        
        $this->model->closeConnect();
        
    }
    
    public function taskComplete($id, $page=false) {
        if($this->authCheck($this->model::ROLE_ADMIN) ==  false){
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
    
    //разлогирование
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
    
    
    //проверка прав доступа
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
    
    //текущий пользователь
    protected function getCurrentUser(){
        $hash = $_COOKIE[$this->hashCookieName];
        $user = $this->model->getUserByHash($hash);
        return $user;
    }
    
}
