<?php

/*
 Original Code by http://www.vampire-blood.net/1170.html
*/

define('OUT_LOGIN_TIME', 30);

function getdirlist($dirpath='' , $flag = true ){
	if ( strcmp($dirpath,'')==0 ) return null;
	$dir_list = array();
	if( ($dir = @opendir($dirpath) ) == FALSE ) {
		return array();
	}
	while ( ($file=readdir( $dir )) !== FALSE ){
		if ( is_dir( "$dirpath/$file" ) ){
			if( strpos( $file ,'.' ) !== 0 ){
				$dir_list[] = $file;
			}
		}
	}
	return $dir_list;
}

function ddf_loadRuby($file) {
	if (file_exists("./src_bcdice/diceBot/".$file.'.rb')) {
		$data = file("./src_bcdice/diceBot/".$file.'.rb');
		foreach($data as $index => $line) {
			if (trim($line) === 'def gameName') {
				return trim(trim($data[$index + 1]),'"'."'");
			}
		}
	}
	return $file;
}

function ddf_loadRooms($savedata_dir,$sorttype='',$game_none='設定なし') {
	$dirs = getdirlist($savedata_dir);
	date_default_timezone_set('Asia/Tokyo');

	$datas = array();
	$gamenames=array();
	$gamenames['diceBot'] = $game_none;
	$gamenames[null] = $game_none;
	$dt = time();
	$dt -= OUT_LOGIN_TIME;
	foreach($dirs as $row => $value) {
		$nowdata = json_decode(file_get_contents($savedata_dir."/".$value.'/playRoomInfo.json'),true);

		$login_num = 0;
		if (file_exists(DDF_PATH."/".$value.'/login.json')) {
			$logindata = json_decode(file_get_contents($savedata_dir."/".$value.'/login.json'),true);
			$nullnames= 0;
			$mt = 0;
			foreach($logindata as $logincode => $loginval) {
				if (strpos($logincode,"\t") !== false) {
					$login_num++;
					if ($loginval['timeSeconds'] <= $dt) {
						$nullnames++;
					}
				}
				$mt = max($mt,$loginval['timeSeconds']);
			}
			$logindata=null;
			if ($nullnames >= $login_num) {
				$login_num = 0;
			}

		}
		$id = substr($value,5);
		if (!isset($gamenames[$nowdata['gameType']])) {
			$gamenames[$nowdata['gameType']] = ddf_loadRuby($nowdata['gameType']);
		}
		$nowgame = $gamenames[$nowdata['gameType']];
		$datas[] = array('id'=>$id-0,'name'=>$nowdata['playRoomName'],'dice'=>$nowgame,
		'visit'=>($nowdata['canVisit']===true),'time'=>$mt,
		'pass'=> ($nowdata['playRoomChangedPassword']!==null),'members'=> $login_num);
	}

	if ($sorttype != '') {
		foreach($datas as $key => $row){
			$dt_id[$key] = $row["id"];
			$dt_name[$key] = $row["name"];
			$dt_system[$key] = $row["dice"];
			$dt_visit[$key] = $row["visit"];
			$dt_time[$key] = $row["time"];
			$dt_pass[$key] = $row["pass"];
			$dt_members[$key] = $row["members"];
		}
		switch($sorttype) {
			case 'ID':case 'id':
			array_multisort($dt_id,SORT_ASC,$datas);
			break;
			case '!ID':case '!id':
			array_multisort($dt_id,SORT_DESC,$datas);
			break;
			case 'Name':case 'name':case 'NAME':
			array_multisort($dt_name,SORT_ASC,$dt_id,SORT_ASC,$datas);
			break;
			case '!Name':case '!name':case '!NAME':
			array_multisort($dt_name,SORT_DESC,$dt_id,SORT_DESC,$datas);
			break;
			case 'Time':case 'time':case 'TIME':
			array_multisort($dt_time,SORT_DESC,$dt_id,SORT_ASC,$datas);
			break;
			case '!Time':case '!time':case '!TIME':
			array_multisort($dt_time,SORT_ASC,$dt_id,SORT_DESC,$datas);
			break;
			case 'Num':case 'num':case 'NUM':
			array_multisort($dt_members,SORT_DESC,$dt_id,SORT_ASC,$datas);
			break;
			case '!Num':case '!num':case '!NUM':
			array_multisort($dt_members,SORT_ASC,$dt_id,SORT_DESC,$datas);
			break;
		}

	}
	return $datas;
}