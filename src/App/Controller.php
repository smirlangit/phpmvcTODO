<?php
namespace App;


class Controller {

    //model component
    protected $model;
    //view component
    protected $view;
    
    
    protected $hashCookieName = 'hash';
    protected $sortCookieName = 'sortingby';
    protected $sortDirCookieName = 'sortdirect';
    
    public function __construct() {
        //use DI principle for creating instance
        $this->model = new Model();
        $this->view = new View();
        
    }
    
    /**
     * Add task to database
     * @param string $name - user name, task creator
     * @param string $mail - user email
     * @param string $desc - description of the task
     * 
     */
    public function addTask($name, $mail, $desc) {
        
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->error('invalid email format');
            return;
          }
          
        //clean js injection
        $name = strip_tags($name);
        $mail = strip_tags($mail);
        $desc = strip_tags($desc);
        
          
        $this->model->addTask($name, $mail, $desc);
        $this->setTasksSort("id");
        $this->showMsg("task added!");
        $this->redirectBack();
        
    }
    
    /**
     * save message text to show on render page
     * @param type $msg
     */
    protected function showMsg($msg) {
        setcookie("msg", $msg, time()+1);
    }
    
    /**
     * 
     * @param int $id
     * @param string $desc
     */
    public function editTask($id,  $desc) {
        
        //check for access by role
        if($this->authCheck($this->model::ROLE_ADMIN) ==  false){
            $this->error('unauthorized access');
            return;
        }
        
        //validate params
        if(is_int($id) == false){
            $this->error('invalid id');
        }
        
        //get logged user
        $user = $this->getCurrentUser();
           
        //save task
        $res = $this->model->taskEdit($id, $desc, $user['login']);
        
        $this->redirectBack();
        
    }

    /**
     * change sorting tasks by column name
     * @param string $sortingBy - column name
     */
    public function setTasksSort($sortingBy) {
        setcookie($this->sortCookieName, $sortingBy);
        
        //toggle sort order, if choosed the same column twice
        $sortDir = '';
        if($sortingBy == $_COOKIE[$this->sortCookieName]){
          $sortDir = $_COOKIE[$this->sortDirCookieName] == 'asc' ? 'desc':'asc';             
            
        } 
        //save sorting name, for all pages
        setcookie($this->sortDirCookieName, $sortDir);
                
        $this->redirectBack();
        
    }


    /*
     * render page with tasks
     * @param int $page - change page number
     */
    public function viewTasksPage($page=0){
        //get saved page number
        $sortingBy = $_COOKIE[$this->sortCookieName];
        //get saved sort order
        $sortDirection = $_COOKIE[$this->sortDirCookieName]=='asc' ? 'ASC' : 'DESC';
        
        //sorting validation
        $validSortFileds = ["user_name", "email", "status", "id"];
        if(in_array($sortingBy, $validSortFileds) == false){
            $sortingBy = "id";
        }
        
        //page num valudation
        if(is_int($page) == false && $page < 0){
            $page = 0;
        }
        
        //get tasks from database
        $tasks = $this->model->getTasks($page, $sortingBy, $sortDirection);
        
        //get user role (admin or guest)
        $isAdmin = false;
        if(isset($_COOKIE[$this->hashCookieName])){
            $isAdmin = $this->model->getAuthByHash($_COOKIE[$this->hashCookieName]);
            
        }
        
        //calculate pages count
        $pageCount = ceil(( $this->model->tasksCount()) /  $this->model->taskPerPage);
        

        //render page
        $this->view->showPage('tasks', ['tasks'=>$tasks, 'isadmin'=>$isAdmin, 'pages'=>$pageCount, 'currpage'=>$page]);
        
        //close database
        $this->model->closeConnect();
        
    }
    
 
    
    /**
     * complete task by id
     * @param type $id
     * @return boolean
     */
    public function taskComplete($id) {
        
        //validate user role
        if($this->authCheck($this->model::ROLE_ADMIN) ==  false){
            $this->error('unauthorized access');
            return false;
        }
        
        //validate task id
        if(is_int($id) == false && $id < 0){
            $this->error('parameters is not valid');
            return false;
        }
        
        //set task status to completed
        $this->model->taskComplete($id);

    }
    
    /**
     * it's just to demonstrate the user's authentication, not for production.
     * authenticate guest user by login and password
     * @param string $user - user login
     * @param string $password - user password
     */
    public function login($user, $password) {        
        //search user by login in database
        $user = $this->model->getUser($user, $password);       
        
        //make new hash if user login founded
        if($user){
            $hash = $this->model->hashGenerate($user["id"]);
            $this->saveAuthHash($hash);
        } else {
            $this->error('incorrect username or password');
        }
        $this->redirectBack();        
        
    }
    
    /**
     * logout current user
     */
    public function logout() {    
        //get current user by hash
        $hash = $_COOKIE[$this->hashCookieName];
        $user = $this->model->getUserByHash($hash);
        
        //generate new hash and save it to database
        $this->model->hashGenerate($user['id']);
        
        //clean hash
        $this->cleanAuthHash();
        
        $this->redirectBack();    
        
    }
    
    /**
     * save auth hash string
     */
    protected function saveAuthHash($hash){
        setcookie($this->hashCookieName, $hash);
    }
    
    /**
     * clean hash string for client
     */
    protected function cleanAuthHash(){
        setcookie($this->hashCookieName, '');
    }

    /**
     * check user role
     * @param type $role
     * @return boolean
     */
    protected function authCheck($role) {
        $checked = false;
        //get user by hash
        $hash = $_COOKIE[$this->hashCookieName];
        $user = $this->model->getUserByHash($hash);
        
        //check user role
        if($user['role'] == $role){
            $checked = true;
        } else {
            $checked = false;
        }
        
        return $checked;
    }
    
    /**
     * current user
     * @return array - user info
     */
    protected function getCurrentUser(){
        $hash = $_COOKIE[$this->hashCookieName];
        $user = $this->model->getUserByHash($hash);
        return $user;
    }
    
    /**
     * show message and redirect to error page
     * @param type $msg
     */
    protected function error($msg){
        $this->showMsg($msg);
        header('Location: /');
    }
    
    /**
     * redirect to back page
     */
    protected function redirectBack(){
        header('Location: /');
    }
    
}
