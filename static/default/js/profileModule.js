var current_city;
var current_country;
var exec_url;

var country_list = {};
var city_list = {};

var countryDiv;
var cityDiv;

function profileModule_cityInit(countrydivid,citydivid,cityid,url){
	exec_url = url;
	countryDiv = document.getElementById(countrydivid);
	cityDiv = document.getElementById(citydivid);
	$.post(exec_url, {
		"jquery": "ProfileModule",
		"action":"init",
		"city_id" : cityid	
	},
	function(data){
		current_city = data.city_id;
		current_country = data.country_id;
		country_list = data.country_list;
		city_list[data.country_id] = data.city_list;
		profileModule_redrawCountryCity();
	}, "json");
}

function profileModule_redrawCountryCity(){
	var options = '';
	for(var i in country_list){
		if(country_list[i].id == current_country)
			options += '<option selected="selected">'+country_list[i].name+'</option>';
		else
			options += '<option>'+country_list[i].name+'</option>';
	}
	countryDiv.innerHTML = '<select onchange="profileModule_countryChange(this)">'+options+'</select>';
	cityDiv.innerHTML = '';
	var cityInput = document.createElement('INPUT');
	cityInput.type = 'hidden';
	cityInput.value = city_list[current_country][current_city].id
	cityDiv.appendChild(cityInput)
	
	var citySelect = document.createElement('SELECT');
	citySelect.name = 'city_id';
	cityDiv.appendChild(citySelect)
	var co = 0;
	for(var k in city_list[current_country]){
		var opt = document.createElement('OPTION');
		opt.value = city_list[current_country][k].id;
		opt.innerHTML = city_list[current_country][k].name;
		co++;
		citySelect.appendChild(opt);
		if(city_list[current_country][k].id == current_city)
			citySelect.selectedIndex = co-1;
		

		
	}
	
}

function profileModule_countryChange(obj){
	for(var i in country_list)
		if(country_list[i].name == obj.value){
			$.post(exec_url, {
				"jquery": "ProfileModule",
				"action":"getCityList",
				"country_id" : country_list[i].id
			},
			function(data){
				current_country = data.country_id;
				current_city = data.city_id;
				city_list[data.country_id] = data.city_list;
				profileModule_redrawCountryCity();
			}, "json");
		}
		
}


////////////////////
var _container;
var _uid;
function profileModule_drawAddFriend(uid){
	profileModule_drawPlank(1,uid);
}

function profileModule_drawRemoveFriend(uid){
	profileModule_drawPlank(2,uid);
}

function profileModule_drawPlank(_type, uid){
	var div = document.createElement('DIV');
	var a = document.createElement('A');
	div.appendChild(a);
	a.innerHTML = (_type==1)?'Убрать из друзей':'Добавить в друзья';
	a.href="javascript:void(0)";
	if(_type == 1){
		a.onclick = function(){
			profileModule_removeFriend(uid);
		}
	}
	else
		a.onclick = function(){
			profileModule_addFriend(uid)
		}
	var con = document.getElementById(_container);
	con.innerHTML = '';
	con.appendChild(div);
	con.style.display = 'block';
}

function profileModule_checkFriend(id, _exec_url , container){
	_container = container;
	exec_url = _exec_url;
	_uid = id;
	_id = id;
	$.post(exec_url, {
		"jquery": "ProfileModule",
		"action":"checkFriend",
		"id" : id	
	},
	function(data){
		if(data.result>-1){
			if(data.result == 1){
				profileModule_drawAddFriend(_uid);
			}else
			if(data.result == 0){
				profileModule_drawRemoveFriend(_uid);
			}
		}
	}, "json");
}
function profileModule_addFriend(id){

	$.post(exec_url, {
		"jquery": "ProfileModule",
		"action":"addFriend",
		"id" : id	
	},
	function(data){
		document.location.reload();
	}, "json");
}

function profileModule_removeFriend(id){

	$.post(exec_url, {
		"jquery": "ProfileModule",
		"action":"removeFriend",
		"id" : id	
	},
	function(data){
		document.location.reload();
	}, "json");
}
