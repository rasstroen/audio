var _bookId = 0;

var shelf_names = {1:'Я читал', 2:'Я читаю', 3:'Я буду читать', 4:'Я плакал'};
var def_error = 'Произошла ошибка связи. Попробуйте повторить позже.';

function delFromShelf(book_id,element) {
  var post_params = {
    jquery: 'books_module',
    action: 'del_from_shelf',
    id: book_id
  };

  $.post(exec_url, post_params, function(data){
    if (data && data.success){
      element.fadeOut();
    }
    else if (data && data.error) {alert(data.error);}
    else {alert(def_error);}
  }, "json");
};

function addToShelf(book_id,shelf_id,element) {
  var post_params = {
    jquery: 'books_module',
    action: 'add_to_shelf',
    id: book_id,
    shelf_id: shelf_id
  };

  $.post(exec_url, post_params, function(data){
    if (data && data.success){
      element.html('Эта книга на вашей полке «'+shelf_names[data.shelf_id]+'»');
    }
    else if (data && data.error) {alert(data.error);}
    else {alert(def_error);}
  }, "json");
};

function checkInShelf(book_id,element) {
  var post_params = {
    jquery: 'books_module',
    action: 'check_in_shelf',
    id: book_id
  };

  $.post(exec_url, post_params, function(data){
    if (data && data.success){
      if(data.shelf_id!=0){
        element.html('Эта книга на вашей полке «'+shelf_names[data.shelf_id]+'»');
      }
    }
    else if (data && data.error) {alert(data.error);}
    else {alert(def_error);}
  }, "json");
};

$(function() {
	function bindDeleteCallbacks(){

		_bookId = $('input[type="hidden"][name="id"]').val();

    $.each(['author', 'genre', 'serie', 'relation'], function(index, value) { 
      $('.books-edit-'+value+'-delete').each(function(i){
        $(this).bind('click', function(){
          $.post(exec_url, {
              "jquery": "books_module",
              "action": "del_"+value,
              "id" : _bookId,
              "item_id" : $(this).next('input').val()
          },
          function(data){
            if(data.success && data.item_id){
              $('.'+value+'-'+data.item_id).remove();
            }
	else if (data && data.error) {
          alert(data.error);
        } else {
          alert(def_error);
        }
          }, "json");
          return false;
        });
      });
    });

  };

	bindDeleteCallbacks();

  $.each(['author', 'genre', 'serie', 'relation'], function(index, value) { 
    $('.books-edit-'+value+'-new-submit').bind('click', function(){
      item_id = $('.books-edit-'+value+'-new-id').val();

      var post_params = {};
      post_params.jquery = 'books_module';
      post_params.id = _bookId;

      switch(value) {
      case 'author':
        post_params.action = 'add_author';
        post_params.id_author = item_id;
        post_params.id_role = $('.role-select').val();;
        break;
      case 'genre':
        post_params.action = 'add_genre';
        post_params.id_genre = item_id;
        break;
      case 'serie':
        post_params.action = 'add_serie';
        post_params.id_serie = item_id;
        break;
      case 'relation':
        post_params.action = 'add_relation';
        post_params.book_id = item_id;
        post_params.relation_type = $('.relation_type-select').val();
        break;
      default:
      }

      $.post(exec_url, post_params, function(data){
        if(data && data.success){
          id = data.item_id;
          new_item = $('.books-edit-'+value+'.hidden').clone();
          new_item.children('input').val(id);
          switch(value) {
          case 'author':
            new_item.children('.books-edit-author-name').html(data.name);
            new_item.children('.books-edit-author-role').html(data.role);
            break;
          case 'genre':
            new_item.children('.books-edit-genre-title').html(data.title);
            break;
          case 'serie':
            new_item.children('.books-edit-serie-title').html(data.title);
            break;
          case 'relation':
            new_item.children('.books-edit-relation-type').html(data.relation_type);
            new_item.children('.books-edit-relation-title').html(data.title.title);
            break;
          default:
          }
          new_item.toggleClass(value+'-'+id);
          new_item.toggleClass('hidden');
          $('.books-edit-'+value+'-new').before(new_item);
        } else if (data && data.error) {
          alert(data.error);
        } else {
          alert('Сервер отказывается это добавлять');
        }
        bindDeleteCallbacks();
      }, "json");
      return false;
    });
  });
});
