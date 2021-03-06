<?php
namespace App;
include __DIR__.DIRECTORY_SEPARATOR.'Bootstrap.php';

use App\Controller;


//контроллер получит http запрос, извлечен данные и передаст дальше модели

//модель обработает и отдаст контроллеру

//полученные данные, контроллер отдаст view

//view оформит данные в виде дизайна, подгрузит стили и js

// так как в задании указан пункт о том что нужно сделать минимальной струкутурой, весь этап просто разобьется на классы mvc без универсальной структуры



$controller = new Controller();


//очень упрощенный роутинг запросов
if($_POST["action"]== 'addtask'){
    $controller->addTask($_POST['name'], $_POST['email'], $_POST['description']);    
}

if($_POST["action"]== 'login'){
    $controller->login($_POST['login'], $_POST['password']);    
}

if($_GET["action"]== 'logout'){
    $controller->logout();    
}

if($_POST["action"]== 'taskcomplete'){    
    $controller->taskComplete($_POST['id'], $_GET["page"]);    
}

if($_POST["action"]== 'taskedit'){
    $controller->editTask($_POST['id'], $_POST['desc'],  $_GET["page"]);    
}

if($_GET["action"]== 'sort'){
    $controller->setTasksSort($_GET['sortby']);    
}


//по умолчанию вывод задач
$controller->viewTasksPage($_GET["page"]);
 
