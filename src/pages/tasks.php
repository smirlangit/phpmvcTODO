<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src='/public/app.js'></script>

<div class="container">
    <p class="mt-5"></p>
    
<?php if($isadmin): ?>   
    <form class="form-inline " method='get'>

       <input type="hidden" name="action" value="logout">
       <button type="submit" class="btn btn-primary mb-2">выход</button>

    </form>
    
<?php else: ?>
    <form class="form-inline " method='post' enctype="multipart/form-data">
      <div class="form-group mx-sm-3 mb-2">    
          <input name='login' type="login" class="form-control"  placeholder="логин" required="">
      </div>    

      <div class="form-group mx-sm-3 mb-2">    
          <input name='password' type="password" class="form-control" placeholder="пароль" value='' required>
      </div>

       <input type="hidden" name="action" value="login">
       <button type="submit" class="btn btn-primary mb-2">вход</button>
    </form>
 <?php endif; ?>    
    

    
 <hr>   
    
    
    <?php if($isadmin): ?>
    администратор
    <?php endif; ?>
    
<div class="row">
    <div class="col-md-12">
            
                   
        <div>
             <h1>Список задач</h1>
             <form method='post' enctype="application/x-www-form-urlencoded">
                    
                    <div class="form-group" >                    
                        <div class="form-inline ">                        
                            <input name='name' type="text" class="form-control" placeholder="имя" required >                          
                            <input name='email' type="email" class="form-control" placeholder="email" required>  
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <input name='description' type="text" class="form-control" placeholder="описание задачи" required>
                    </div>
                    <input type='hidden' name='action' value='addtask'>
                    <button type="submit" class="btn btn-primary" >добавить</button>  
                </form>
                <hr>
                <p>сортировка</p>
                <a href="?action=sort&sortby=user_name">по имени</a> | 
                <a href="?action=sort&sortby=email">по email</a> | 
                <a href="?action=sort&sortby=status">по статусу</a>  |
                <a href="?action=sort&sortby=id">(обычная)</a> 
                <hr>
                <table class="table table-striped">
                    <thead>
                        <tr>
                          <th>Имя</th>
                          <th>Email</th>
                          <th class="col-md-12">Описание задачи</th>
                          
                          
                            <?php if($isadmin): ?>

                              <th>сохранить</th>
                              <th>завершить</th>                            
                            <?php endif; ?>
                              
                            <th>отредактировано администратором</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($tasks as $task): ?>
                          <tr <?php if( $task["status"]=='done' ): ?> style='text-decoration: line-through' <?php endif; ?>>
                            <td><?= base64_decode ($task["user_name"]) ?></td>
                            <td><?= base64_decode($task["email"]) ?></td>
                            
                            
                            <?php if($isadmin): ?>
                                
                                <td> <input id="edit_field_<?= $task["id"]?>" type='text' name="desc" value='<?= base64_decode($task["description"]) ?>' class='form-control'></td>
                                <td> <button type="submit" class="btn btn-default"   onclick="edit('<?= $task["id"]?>')">сохранить</button>  </td>
                                <td><a href ='#'onclick="taskComplete('<?= $task["id"]?>')" ><input type="checkbox" <?= $task["status"]=='done'? 'checked':'' ?>></a></td>
                             <?php else: ?>
                                <td><?= base64_decode($task["description"]) ?></td>                                
                             <?php endif; ?>
                              
                            <td><?= $task["editby"]!=null ?'да':'' ?></td>
                            
                          </tr>
                         <?php endforeach; ?>
                        
                          
                      </tbody>
                </table>
                    
        </div>
        
        
            <hr>
             <ul class="pagination">
                 <?php for($i=0; $i<$pages; $i++): ?>
                 <li class="page-item"><a class="page-link" href="?page= <?= $i ?>"><?= $i +1 ?></a></li>
                 <?php endfor; ?>
              </ul> 
            
        </div>
        
    </div>
</div>

<!-- редактирование описания -->
<form method="post" id="edittaskform">
    <input type="hidden" id="edittask_id" name="id" value="">
    <input type="hidden" id="edittask_desc" name="desc" value="">
    <input type="hidden" name="action" value="taskedit">
</form>

<!-- завершение задачи -->
<form method="post" id="completetaskform">
    <input type="hidden" id="complete_id" name="id" value="">
    <input type="hidden" name="action" value="taskcomplete">
</form>

