$(function() {
  $.each(['author', 'book', 'genre', 'serie'], function(index, value) {
    $('.add-loved-'+value).each(function(){
      id = $('.'+value+'s-show input[type=hidden]').val();
      checkLoved(value,id,$(this));
    });

    $('.add-loved-'+value).bind('click',function(){
      id = $('.'+value+'s-show input[type=hidden]').val();
      addToLoved(value,id,$(this));
    });
  });

  book_id = $('.books-show input[type=hidden]').val();

  if (book_id) {
    checkInShelf(book_id,$('.book-shelf-info-shelf-name'));

    $('.add-to-shelf').bind('click', function(){
      $('.add-to-shelf-form').toggle();
    });

    $('.add-to-shelf-confirm').bind('click',function(){
      shelf_id = $('select[name="add-to-shelf-shelf-id"]').val();
      addToShelf(book_id,shelf_id,$('.book-shelf-info-shelf-name'));
    });
  }

});
