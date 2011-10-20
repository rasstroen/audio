var tr = {}
tr['book'] = ['Книга', 'книгу'];
tr['author'] = ['Автор', 'автора'];
tr['serie'] = ['Серия', 'серию'];
tr['genre'] = ['Жанр', 'жанр'];

function addToLoved(item_type,item_id,element){
  var post_params = {};
  post_params.jquery = 'users_module';
  post_params.action = 'add_loved';
  post_params.item_type = item_type;
  post_params.item_id = item_id;

  $.post(exec_url, post_params, function(data){
    if (data && data.success){
      if (data.in_loved) {
        element.parent().children('p').html(tr[item_type][0]+' у вас в любимых');
        element.html('Убрать?');
      } else {
        element.parent().children('p').html('');
        element.html('Добавить '+tr[item_type][1]+' в любимые');
      }
    } else if (data && data.error) {
      alert(data.error);
    } else {
      alert('Сервер отказывается это добавлять');
    }
  }, "json");
};

function checkLoved(item_type,item_id,element){
  var post_params = {};
  post_params.jquery = 'users_module';
  post_params.action = 'check_loved';
  post_params.item_type = item_type;
  post_params.item_id = item_id;

  $.post(exec_url, post_params, function(data){
    if (data && data.success){
      if (data.in_loved) {
        element.parent().children('p').html(tr[item_type][0]+' у вас в любимых');
        element.html('Убрать?');
      }
    } else if (data && data.error) {
      alert(data.error);
    } else {
      alert('Сервер отказывается это добавлять');
    }
  }, "json");

};
