var def_error = 'Произошла ошибка связи. Попробуйте повторить позже.';

roles = {0:'Аноним', 10:'Неподтвержденный', 20:'Вандал', 30:'Читатель', 40:'Библиотекарь', 50: 'Администратор'}

$(function() {
  user_id = $('input[type="hidden"][name="id"]').val();
  role = $('users-show-text-role');
  $('.make-vandal').bind('click',function(){
    var post_params = {};
    post_params.jquery = 'users_module';
    post_params.action = 'toggle_vandal';
    post_params.id = user_id;

    $.post(exec_url, post_params, function(data){
      if (data && data.success){
        $('.users-show-text-role').hide().html(roles[data.user_role]).fadeIn();
        $(this).html('Сделать читателем')
      }
      else if (data && data.error) {alert(data.error);}
      else {alert(def_error);}
    }, "json");

    return false;
  });
});
