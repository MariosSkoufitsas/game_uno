<?php
	function show_status() {
		global $mysqli;
		check_for_uno();
		check_abort();
		check_ended();
		$sql = 'select * from game_status';
		$st = $mysqli->prepare($sql);
		$st->execute();
		$res = $st->get_result();
		header('Content-type: application/json');
		print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
	}
	
	function check_for_uno(){
		global $mysqli;
		$sqlc='select count(*) as c from hand h inner join game_status g on h.player_name=g.p_turn';
		$st=$mysqli->prepare($sqlc);
		$st->execute();
		$res=$st->get_result();
		$counter=$res->fetch_assoc()['c'];
		$sqls='select uno_status, p_turn from player p inner join game_status g on p.player_name=g.p_turn';
		$st=$mysqli->prepare($sqls);
		$st->execute();
		$res=$st->get_result();
		$row=$res->fetch_assoc();
		$player=$row['p_turn'];
		$uno=$row['uno_status'];
		if(($counter<2) && ($uno=='not active')){
			$sqlp='call general_draw(?)';
			for($i=0; $i<=2; $i++){
				$st=$mysqli->prepare($sqlp);
				$st->bind_param('s',$player);
				$st->execute();
			}
		}
	}
	
	function check_ended(){
		global $mysqli;
		$sqlc1 = 'select count(*) as c from hand where player_name="p1"';
		$st = $mysqli->prepare($sqlc1);
		$st->execute();
		$res = $st->get_result();
		$counter_p1 = $res->fetch_assoc()['c'];
		$sqlc2 = 'select count(*) as c from hand where player_name="p2"';
		$st = $mysqli->prepare($sqlc2);
		$st->execute();
		$res = $st->get_result();
		$counter_p2 = $res->fetch_assoc()['c'];
		if ($counter_p1==0){
			$sql = "update game_status set status='ended', p_turn=null, result='p1'";
			$st = $mysqli->prepare($sql);
			$st->execute();
		}
		else if ($counter_p2==0){
			$sql = "update game_status set status='ended', p_turn=null, result='p2'";
			$st = $mysqli->prepare($sql);
			$st->execute();
		}
		
	}
	
	function pass_status(){
		global $mysqli;
		$sql = 'call pass()';
		$st = $mysqli->prepare($sql);
		$st->execute();
		show_status();
	}
	
	function check_abort(){
		global $mysqli;
		$sql="update game_status set status='aborded', result=if(p_turn='p1','p2','p1'),p_turn=null where p_turn is not null and last_change<(now()-INTERVAL 5 MINUTE) and status='started'";
		$st=$mysqli->prepare($sql);
		$res=$st->execute();
	}
	
	function update_game_status(){
		global $mysqli;
		$sql='select * from game_status';
		$st=$mysqli->prepare($sql);
		$st->execute();
		$res=$st->get_result();
		$status=$res->fetch_assoc();
		$new_status=null;
		$new_turn=null;
		$st3=$mysqli->prepare('select count(*) as aborted from player WHERE last_action< (NOW() - INTERVAL 5 MINUTE)');
		$st3->execute();
		$res3=$st3->get_result();
		$aborted=$res3->fetch_assoc()['aborted'];
		if($aborted>0){
			$sql="UPDATE player SET username=NULL, token=NULL WHERE last_action< (NOW() - INTERVAL 5 MINUTE)";
			$st2=$mysqli->prepare($sql);
			$st2->execute();
			if ($status['status']=='started'){
				$new_status='aborted';
			}
		}
		$sql='select count(*) as c from player where username is not null';
		$st=$mysqli->prepare($sql);
		$st->execute();
		$res=$st->get_result();
		$active_players=$res->fetch_assoc()['c'];
		switch($active_players){
			case 0: $new_status='not active';
				break;
			case 1: $new_status='initialized';
				break;
			case 2: $new_status='started';
					if($status['p_turn']==null){
						$new_turn='p1';
					}
				break;	
		}
		$sql = 'update game_status set status=?, p_turn=?';
		$st = $mysqli->prepare($sql);
		$st->bind_param('ss',$new_status,$new_turn);
		$st->execute();
	}
	
	
?>