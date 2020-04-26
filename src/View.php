<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of View
 *
 * @author mirlan
 */
class View {
    //папка где хранятся стили, скрипты и страницы оформления
    protected $public = 'pages';
    protected $ext = '.php';
    
    public function __construct() {
        //абсолютный путь до публичной папки
        $this->public = ROOT.DIRECTORY_SEPARATOR.$this->public.DIRECTORY_SEPARATOR;
    }
    
    public function showPage($pageName, $data){
        $page = $this->public.$pageName;
        extract($data);
        include_once ($page.$this->ext);
        
    }
}
