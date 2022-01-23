<?php

function start_game(){
	global $mysqli;
	$sqlp1 = 'call start_cards("p1")';
	$sqlp2 = 'call start_cards("p2")';
	$sqlp3 = 'call start_game()'; 
	for ($i = 0; $i <=6; $i++){
		$mysqli->query($sqlp1);
		$mysqli->query($sqlp2);
	}
	$mysqli->query($sqlp3);
	$sql = 'select h.player_name, d.card_code from hand h inner join deck d on h.card_id=d.card_id';
	$st = $mysqli->query($sql);
	$sql1 = 'select * from table_deck order by table_id desc limit 1';
	$st1 = $mysqli->query($sql1);
	$res1 = $st->fetch_all(MYSQLI_ASSOC);
	$res2 = $st1->fetch_all(MYSQLI_ASSOC);
	header('Content-type: application/json');
	print json_encode(array($res1,$res2), JSON_PRETTY_PRINT);
}

function show_game(){
	global $mysqli;
	$sql = 'select h.player_name, d.card_code from hand h inner join deck d on h.card_id=d.card_id';
	$sql1 = 'select * from table_deck order by table_id desc limit 1';
	$st = $mysqli->query($sql);
	$st1 = $mysqli->query($sql1);
	$res1 = $st->fetch_all(MYSQLI_ASSOC);
	$res2 = $st1->fetch_all(MYSQLI_ASSOC);
	header('Content-type: application/json');
	print json_encode(array($res1,$res2), JSON_PRETTY_PRINT);
}

function reset_game(){
	global $mysqli;
	$sql = 'call clean_deck()';
	$mysqli->query($sql);
	start_game();
}

function draw_card(){
	global $mysqli;
	$sql = 'select p_turn from game_status limit 1';
	$st=$mysqli->query($sql);
	$res=$st->fetch_assoc();
	$st2=$mysqli->prepare('call draw_card(?)');
	$st2->bind_param('s', $res['p_turn']);
	$st2->execute();
	show_game();	
}

function make_move($card){
	global $mysqli;
	$sql = 'select d.card_color, d.card_symbol from table_deck t inner join deck d on d.card_code=t.card_code order by table_id desc limit 1';
	$st=$mysqli->query($sql);
	$res=$st->fetch_assoc();
	$symbol=$res['card_symbol'];
	$color=$res['card_color'];
	$st2=$mysqli->prepare('call check_do_move(?,?,?)');
	$st2->bind_param('sss',$color, $symbol, $card);
	$st2->execute();
	show_game();
}

function make_w_move($card_code, $card_color){
	global $mysqli;
	$st=$mysqli->prepare('call do_wildcard_move(?,?)');
	$st->bind_param('ss', $card_code, $card_color);
	$st->execute();
	show_game();
}


function uno_status(){
	global $mysqli;
	$sqlc='select count(*) as c from hand h inner join game_status g on h.player_name=g.p_turn';
	$st=$mysqli->prepare($sqlc);
	$st->execute();
	$res=$st->get_result();
	$counter=$res->fetch_assoc()['c'];
	if($counter>2){
		header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg'=>"You can't press uno yet."]);
        exit;
	}
	$sql='call uno_status()';
	$st=$mysqli->prepare($sql);
	$st->execute();
	show_uno();
}

function show_uno(){
	global $mysqli;
	$sql='select p.uno_status from player p inner join game_status g on g.p_turn=p.player_name';
	$st=$mysqli->prepare($sql);
	$st->execute();
	$res=$st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}

?>