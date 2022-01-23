var me={};
var game_status={};

$(function () {
	fill_game();
	$('#do_move').click(do_move);
	$('#uno_game_reset').click(do_reset);
	$('#say_uno').click(say_uno);
	$('#draw').click(draw);
	$('#pass').click(pass);
	$('#uno_login').click(login_to_game);
    $('#do_move').prop('disabled', true);
    $('#nextmove').prop('disabled', true);
    $('#pass').prop('disabled', true);
    $('#draw').prop('disabled', true);
    $('#say_uno').prop('disabled', true);
    //$('#pass_div').hide();
    //$('#uno_card').hide();
    //$('#deck').hide();
    game_status_update();
});


function fill_game(){	
	$.ajax({type:"GET", url: "uno.php/game/", dataType:"json", success: fill_game_by_data });
	$.ajax({type:"GET", url: "uno.php/players/", dataType:"json", success: fill_players });
	player_turn();
	//$.ajax({type:"GET", url: "uno.php/status/", dataType:"json", success: player_turn });
}

function fill_game_by_data(data){
	$('#player1_cards').html("Player1: ");
	$('#player2_cards').html("Player2: ");
	for(var i=0; i<data.length; i++){
		for(var j=0; j<data[i].length; j++){
			var obj = data[i][j];
			if(i==0){
				if(obj.player_name=='p1' && obj.player_name==me.player_name){
					$('#player1_cards').append(obj.card_code);
					$('#player1_cards').append("&nbsp;&nbsp;&nbsp;&nbsp;");
				}else if(obj.player_name=='p2' && obj.player_name==me.player_name){
					$('#player2_cards').append(obj.card_code);
					$('#player2_cards').append("&nbsp;&nbsp;&nbsp;&nbsp;");
				}else if(obj.player_name=='p1'){
					$('#player1_cards').append("[]");
					$('#player1_cards').append("&nbsp;&nbsp;&nbsp;&nbsp;");
				}else if(obj.player_name=='p2'){
					$('#player2_cards').append("[]");
					$('#player2_cards').append("&nbsp;&nbsp;&nbsp;&nbsp;");
				}
			}
			else{
				if(game_status.status=='started'){
					$('#table_card').html(obj.card_code);
					if (obj.wild_card_color!=null){
						$('#table_card').append(obj.wild_card_color);
					}
				}else{
					$('#table_card').html("[]");
				}
			}
		}
	}
}

function login_to_game(){
	if($('#username').val()==''){
		alert('You have to set a username');
		return;
	}
	var p_name = $('#pname').val();
	fill_game();
	$.ajax({type: 'PUT', url: "uno.php/players/"+p_name,
			dataType: "json", contentType: 'application/json',
			data: JSON.stringify( {username: $('#username').val(), player_name: p_name}),
			success: login_result, error: login_error});
	$('#username').val('');
	$('#pname').val('p1');						
}

function login_result(data){
	me = data[0];
	$('#game_initializer').hide();
	update_info();
	game_status_update();
}

function login_error(data,y,z,c) {
	var x = data.responseJSON;
	alert(x.errormesg);
}

function pass(e){
	$.ajax({type:"PUT", url: "uno.php/game/pass", dataType:"json", success: player_turn });	
}

function draw(e){
	$.ajax({type:"PUT",url:"uno.php/game/draw", dataType:"json", success:fill_game_by_data});
}

function do_reset(e) {
	$.ajax({type: 'POST', url: "uno.php/game/", dataType: "json", 
			success: fill_game_by_data,
			//error: alert("error")
			});
	$.ajax({type:"GET", url: "uno.php/players/", dataType:"json", success: fill_players });
	player_turn();
	me={};
	game_status={};
	$('#game_initializer').show();
	$('#say_uno').removeClass("btn-warning");
    $('#say_uno').addClass("btn-danger");
	//setTimeout(function (){
	//	$.ajax({type:"GET", url: "uno.php/status/", dataType:"json", success: player_turn });
	//}, 100);
}

function game_status_update(){
	$.ajax({url:"uno.php/status/", success:update_status});
}

function update_status(data) {
	game_status=data[0];
	update_info();
	if(game_status.p_turn==me.player_name &&  me.player_name!=null) {
		//x=0;
		// do play
		$('#do_move').prop('disabled', false);
		$('#nextmove').prop('disabled', false);
		$('#pass').prop('disabled', false);
		$('#draw').prop('disabled', false);
		$('#say_uno').prop('disabled', false);
		setTimeout(function() { 
			game_status_update(); 
			if (game_status.status=='started'){
				get_uno(); 
			}
		}, 4000);
	} else {
		// must wait for something
		$('#do_move').prop('disabled', true);
		$('#nextmove').prop('disabled', true);
		$('#pass').prop('disabled', true);
		$('#draw').prop('disabled', true);
		$('#say_uno').prop('disabled', true);
		setTimeout(function() { 
			game_status_update(); 
			if (game_status.status=='started'){
				get_uno(); 
			}
		}, 4000);
	} 	
}

function update_info(){
	$('#game_info').html("I am Player: "+me.player_name+", my name is "+me.username +'<br>Token='+me.token+'<br>Game state: '+game_status.status+'<br>Game result: '+game_status.result);
	fill_game();	
}

function player_turn(){
	if(game_status.p_turn=='p1'){
		$('#p1_turn').addClass("active");
		$('#p2_turn').removeClass("active");
	}
	else if(game_status.p_turn=='p2'){
		$('#p2_turn').addClass("active");
		$('#p1_turn').removeClass("active");
		
	}
	else{
		$('#p2_turn').removeClass("active");
		$('#p1_turn').removeClass("active");
	}
}

function fill_players(data){
	for(var i=0; i<data.length; i++){
		var obj=data[i];
		if(obj.player_name=='p1'){
			$('#player1').val(obj.username);
		}
		else if(obj.player_name=='p2'){
			$('#player2').val(obj.username);
		}
	}
}

function do_move(e){
	var x=$('#nextmove').val();
	var xa = x.split(' ');
	var xcode = xa[0];
	var xcolor = xa[1];
	$('#nextmove').val('');
	var obj;
	if(typeof xcolor == 'undefined'){
		obj={card_code:xcode};	
	}
	else {
		obj={card_code:xcode, card_color:xcolor};
	}	
	var a = JSON.stringify(obj);
	$.ajax({url:"uno.php/game/card", type:'PUT', data:a,
			headers: { "Content-Type": "application/json"}, 
            success: fill_game_by_data
			});
	player_turn();
}

function get_uno(){
	$.ajax({url:"uno.php/game/uno", dataType:"json", success: change_uno_btn});
}

function change_uno_btn(data){
	var obj=data[0].uno_status;
	if(obj=='active'){
		$('#say_uno').removeClass("btn-danger");
        $('#say_uno').addClass("btn-warning");
	}
	else{
		$('#say_uno').removeClass("btn-warning");
        $('#say_uno').addClass("btn-danger");
	}
}

function say_uno(e){
	$.ajax({type:'PUT', url: 'uno.php/game/uno', dataType:"json", success: change_uno_btn, error: uno_btn_error});
}

function uno_btn_error(data,y,z,c) {
    var x = data.responseJSON;
    alert(x.errormesg);
}