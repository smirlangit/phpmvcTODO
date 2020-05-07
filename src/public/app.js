 function edit(id){
     var desc = $('#edit_field_'+id).val();
     $('#edittask_id').val(id);
     $('#edittask_desc').val(desc);
     $('#edittaskform').submit();
 }
 
 function taskComplete(id){
     $('#complete_id').val(id);
     $('#completetaskform').submit();
 }
 
 function checkTaskForm(){
     return false;
 }
 
 function getCookie(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2) return parts.pop().split(";").shift();
}

 $(document).ready(function(){   
     var msg = getCookie('msg');
     if(msg != undefined){
         msg.replace("+", " ");
         alert(unescape(msg));
     }
     
 });
