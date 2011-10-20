var def_error = 'Произошла ошибка связи. Попробуйте повторить позже.';

$(function() {
	$('.messages-list-item-text-delete').bind('click', function(){
		var message_id = $(this).parents('li').attr('id').split('-')[2];
		var thread_id = $(this).parents('li').attr('id').split('-')[1];

		var post_params = {};
		post_params.jquery = 'messages_module';
		post_params.action = 'del_thread';
		post_params.id = thread_id;

		$.post(exec_url, post_params,
			function(data){
				if(data && data.success){
					$('#message-'+thread_id+'-'+message_id).fadeOut();
				} else if (data && data.error) {
					alert(data.error);
				} else {
					alert(def_error);
				}
				bindDeleteCallbacks();
			}, "json");
		return false;
	});
});
