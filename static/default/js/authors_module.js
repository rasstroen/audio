var _authorId = 0;
var def_error = 'Произошла ошибка связи. Попробуйте повторить позже.';

$(function() {
  function bindDeleteCallbacks(){
    _authorId = $('input[type="hidden"][name="id"]').val();
    $('.authors-edit-relation-delete').each(function(i){
      $(this).bind('click', function(){
        $.post(exec_url, {
            "jquery": "authors_module",
            "action": "del_relation",
            "id" : _authorId,
            "item_id" : $(this).next('input').val()
        },
        function(data){
          if(data.success && data.item_id){
            $('.relation-'+data.item_id).remove();
          }
          else if (data && data.error) {alert(data.error);}
          else {alert(def_error);}
        }, "json");
        return false;
      });
    });
  };

	bindDeleteCallbacks();

  $('.authors-edit-relation-new-submit').bind('click', function(){
    item_id = $('.authors-edit-relation-new-id').val();

    post_params = {};
    post_params.jquery = 'authors_module';
    post_params.action = 'add_relation';
    post_params.id = _authorId;
    post_params.author_id = item_id;
    post_params.relation_type = $('.relation_type-select').val();

    $.post(exec_url, post_params,
      function(data){
        if(data && data.success){
          id = data.item_id;
          new_item = $('.authors-edit-relation.hidden').clone();
          new_item.children('input').val(id);
          new_item.children('.authors-edit-relation-type').html(data.relation_type);
          new_item.children('.authors-edit-relation-title').html(data.title);
          new_item.toggleClass('relation-'+id);
          new_item.toggleClass('hidden');
          $('.authors-edit-relation-new').before(new_item);
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
