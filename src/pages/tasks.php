<?php
//$tasks - список задач 
//$isadmin - пользователь является админом


$currpage = isset($_GET['page']) ? $_GET['page'] : '';

?>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>


<div class="container">
    <p class="mt-5"></p>
    
   
<form class="form-inline " action='get'>
  <div class="form-group mx-sm-3 mb-2">    
      <input name='login' type="login" class="form-control"  placeholder="логин" required="">
  </div>
    
    
  <div class="form-group mx-sm-3 mb-2">    
      <input name='password' type="password" class="form-control" placeholder="пароль" value='' required>
  </div>
    
   <input type="hidden" name="action" value="login">
  <button type="submit" class="btn btn-primary mb-2">вход</button>
</form>
    
    
    <?php if($isadmin): ?>
    администратор
    <?php endif; ?>
    
<div class="row">
    <div class="col-md-6">
            
                   
        <div>
             <h1>Список задач</h1>
                <form method='get'>
                    
                    <div class="form-group">                    
                        <div class="form-inline ">                        
                            <input name='name' type="text" class="form-control" placeholder="имя" required >                          
                            <input name='email' type="text" class="form-control" placeholder="email" required>  
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <input name='description' type="text" class="form-control" placeholder="описание задачи" required>
                    </div>
                    <input type='hidden' name='action' value='addtask'>
                    <button type="submit" class="btn btn-primary">добавить</button>  
                </form>
                <hr>
                <p>сортровка</p>
                <a href="?action=sort&sortby=user_name">по имени</a> | 
                <a href="?action=sort&sortby=email">по email</a> | 
                <a href="?action=sort&sortby=status">по статусу</a> 
                <hr>
                    
                    <ul id="sortable" class="list-unstyled"   >
                         <?php foreach($tasks as $task): ?>
                            <li class="ui-state-default"  <?php if( $task["status"]=='done' ): ?> style='text-decoration: line-through' <?php endif; ?>  >
                                        
                                <?= base64_decode ($task["user_name"]) ?> (<?= base64_decode($task["email"]) ?>):

                                <?php if($isadmin): ?>
                                    
                                        <input id="<?= $task["id"]?>" type='text' name="desc" value='<?= base64_decode($task["description"]) ?>'>
                                        <button type="submit" class="btn btn-primary"   onclick="edit('<?= $task["id"]?>')">ok</button>  
                                        <a href ='?action=taskcomplete&task=<?= $task["id"]?><?= $currpage ?>' ><input type="checkbox" <?= $task["status"]=='done'? 'checked':'' ?>></a>

                                <?php else: ?>
                                    <?= base64_decode ($task["description"]) ?>
                                <?php endif; ?>
                            </li>

                         <?php endforeach; ?>
                    </ul>
                
        </div>
            
             <ul class="pagination">
                 <?php for($i=0; $i<$pages; $i++): ?>
                 <li class="page-item"><a class="page-link" href="?page= <?= $i ?>"><?= $i +1 ?></a></li>
                 <?php endfor; ?>
              </ul> 
            
        </div>
        
    </div>
</div>

<form action="get" id="edittaskform">
    <input type="hidden" id="edittask_id" name="id" value="">
    <input type="hidden" id="edittask_desc" name="desc" value="">
    <input type="hidden" id="currpage" name="page" value="">
    <input type="hidden" name="action" value="taskedit">
</form>

<script>
 function edit(id){
     var desc = $('#'+id).val();
     $('#edittask_id').val(id);
     $('#edittask_desc').val(desc);
     $('#currpage').val(<?= $currpage ?>);
     $('#edittaskform').submit();
 }

</script>

    