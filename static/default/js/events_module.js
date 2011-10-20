var exec_url;
function events_module_getLikes(_exec_url){
	exec_url = _exec_url;
	var wall_items = document.getElementsByName('likes');
	var wall = {};
	for(var i in wall_items){
		wall[i] =wall_items[i].id;
	}
	
	$.post(exec_url, {
		"jquery": "events_module",
		"action": "getLikes",
		"wall" : wall
	},
	function(data){
		if(data){
			events_module_drawLikes(data);
		}
	}, "json");
}

function like(id){
	$.post(exec_url, {
		"jquery": "events_module",
		"action": "likeEvent",
		"id" : id
	},
	function(data){
		if(data){
			history.go(0)();
		}
	}, "json");
}

function unlike(id){
	$.post(exec_url, {
		"jquery": "events_module",
		"action": "unlikeEvent",
		"id" : id
	},
	function(data){
		if(data){
			history.go(0)();	
		}
	}, "json");
}

function _setEvent(func,attrs){
	return function(){
		func(attrs);
	}
}

function events_module_drawLikes(data){
	if(data && data.likes){
		var wall_items = document.getElementsByName('likes');
		for(var i in wall_items){
			var likePlankLike = document.createElement('A');
			likePlankLike.style.cursor = 'pointer';
			likePlankLike.id = 'like_'+wall_items[i].id;
			var likePlankCounter = document.createElement('DIV');
      if(wall_items[i] && wall_items[i].id && data.likes[wall_items[i].id]){
        likePlankCounter.innerHTML = '<em>'+data.likes[wall_items[i].id]['count']+'</em> лайков';
        if(!data.likes[wall_items[i].id]['can']){
          likePlankLike.innerHTML = 'нравится';
          likePlankLike.onclick = _setEvent(like,wall_items[i].id);
        }else{
          likePlankLike.innerHTML = 'разонравилось';
          likePlankLike.onclick =_setEvent(unlike,wall_items[i].id);
        }
        wall_items[i].appendChild(likePlankCounter);
        wall_items[i].appendChild(likePlankLike);
      }
		}
	}
}

$(function() {
  $('.add-comment').bind('click',function(){
    $(this).parent().next('.events-list-item-comments-new').toggle();
    return false;
  });
});
