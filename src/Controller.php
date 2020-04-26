<?php


class Controller {

    protected $model;
    protected $view;
    protected $hashCookieName = 'hash';
    
    public function __construct() {
        
        //классы создаются отдельно от места их использования, для того чтобы использующие их методы, не зависили от метода создания объектов
        $this->model = new Model();
        //нужно сделать объект view как singleton, так как он нужен в едином экземпляре, но для упрощения кода, объект создается как обычно
        $this->view = new View();
        
    }
    
    public function addTask($name, $mail, $desc) {
        $res = $this->model->addTask($name, $mail, $desc);
        header('Location: /');
        
    }
    
    public function editTask($id,  $desc, $page=1) {
        $res = $this->model->taskEdit($id, $desc);
       
        header('Location: /?page='.$page);
        
    }
    
    public function setTasksSort($sortingBy) {
        setcookie("sortingby", $sortingBy);
        
    }


    //страницы с пагинацией
    public function viewTasksPage($page=0){
        
        $sortingBy = $_COOKIE['sortingby'];
        $sortDirection = 'ASC';
        
        //получаем данные из модели
        $tasks = $this->model->getTasks($page, $sortingBy, $sortDirection);
        
        //проверка прав доступа
        $isAdmin = false;
        if(isset($_COOKIE[$this->hashCookieName])){
            $isAdmin = $this->model->getAuthByHash($_COOKIE[$this->hashCookieName]);
            
        }
        
        $pageCount = ceil((count($tasks) +1) /  $this->model->taskPerPage);

        
        //отдаем данные на показ через view. Так как нужно максимально упростить код, для каждого рендера делается свой метод, вместо универсального
        $this->view->showPage("tasks", ["tasks"=>$tasks, "isadmin"=>$isAdmin, "pages"=>$pageCount]);
        
        $this->model->closeConnect();
        
    }
    
    public function taskComplete($id, $page=false) {
        $this->model->taskComplete($id);

    }
    
    public function login($user, $password) {
        
        //поиск пользователя по логину и паролю
        $user = $this->model->getUser($user, $password);
        
        
        //если найден, генерируем для него новый хэш
        if($user){
            $hash = $this->model->hashGenerate($user["id"]);
            setcookie($this->hashCookieName, $hash);
        }
        header('Location: /');
        
        
    }
    
}
