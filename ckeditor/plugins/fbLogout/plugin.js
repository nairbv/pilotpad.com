(function(){
//Section 1 : Code to execute when the toolbar button is pressed
var a= {
exec:function(editor){
 window.location='/logout.php';
}
},

//Section 2 : Create the button and add the functionality to it
b='fbLogout';
CKEDITOR.plugins.add(b,{

init:function(editor){

editor.addCommand(b,a);

editor.ui.addButton("fbLogout",{
    label:'Logout', 
    icon:'http://static.ak.fbcdn.net/images/fbconnect/logout-buttons/logout_small.gif',
    command:b,
    });
}
}); 
})();


