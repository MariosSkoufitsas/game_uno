<?php
	require_once "lib/dbconnect.php";
	require_once "lib/test.php";
	require_once "lib/game.php";
	require_once "lib/users.php";
	
	$method = $_SERVER['REQUEST_METHOD'];
	$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
	$input = json_decode(file_get_contents('php://input'),true);
	
	switch ($r=array_shift($request)){
		case 'game' :
			switch ($b=array_shift($request)){
				case '':
				case null: handle_game($method);
					break;
				case 'draw': draw_card();
					break;
				case 'pass': pass_status();
					break;
				case 'card': handle_card($method, $input);
					break;
				case 'uno':change_uno($method);
					break;
				default: header("HTTP/1.1 404 Not Found");
					break;
			}
			break;
		case 'status':
			if(sizeof($request)==0){
				show_status();
			}
			else{
				header("HTTP/1.1 404 Not Found");
			}
			break;
		case 'players': handle_player($method, $request, $input );
			break;
		default: header("HTTP/1.1 404 Not Found");
			exit;
	}
	
	function handle_game($method){
		if($method=='GET'){
			
			show_game();
			
		}
		else if($method=='POST'){
			reset_game();
		}
	}
	function handle_player($method, $request, $input){
		switch ($b=array_shift($request)){
			case '':
			case null:
				if($method=='GET'){
					show_users($method);
				}
				else{
					header("HTTP/1.1 400 Bad Request"); 
                    print json_encode(['errormesg'=>"Method $method not allowed here."]);
				}
				break;
			case 'p1':
			case 'p2': handle_user($method, $b, $input);
				break;
			default: header("HTTP/1.1 404 Not Found");
					 print json_encode(['errormesg'=>"Player $b not found."]);
                break;
		}
	}
	function handle_card($method, $input){
		if($method=='PUT'){
			if(count($input)==1){
				make_move($input['card_code']);
			}
			else if(count($input)==2){
				make_w_move($input['card_code'], $input['card_color']);
			}
		}
	}
	
	function change_uno($method){
		if($method=='PUT'){
			uno_status();
		}
		else if($method=='GET'){
			show_uno();
		}
	}
?>