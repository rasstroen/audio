$(function() {
  $('.del-from-shelf').each(function(){
    var element = $(this).parents('li');
    var book_id = element.attr('id').split('-')[1];
    $(this).bind('click', function(){
      delFromShelf(book_id,element);
    });
  });
});
