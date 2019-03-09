<?php
require_once 'dbconnectinfo.php';
require_once 'APIInfo.php';

$data = json_decode(file_get_contents('php://input'));
switch ($data->type) {
	case 'confirmation': echo $confirmation; break;
	case 'message_edit':
	case 'message_new':
		echo "ok";
		$whitelist = array(246255593);
		$peer_id = $data->object->from_id;
		$user_id = $data->object->from_id;
		$text = $data->object->text;
		if(in_array($user_id, $whitelist))
		{
			$res = explode('\\', $text);
			if(mb_strtolower($res[0]) == "др"){
				$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
				if(mysqli_query($link, "UPDATE Homeworkdata SET Homework='".$res[2]."' WHERE Subject='".$res[1]."'"))
				{
					$message = "Задание по предмету ".$res[1]." записано";
				}
				else {
					$message = "Ошибка в запросе. "."UPDATE Homeworkdata SET Homework='".$res[2]."' WHERE Subject='".$res[1]."'";
				}
				if(mb_strtolower($res[0]) === "Замены"){
					$f = fopen("publicinfo/changes", "w");
					if($res[1]){
						fputs($f, $res[1]);
						$message = "замены выписаны";
					}
				}
				$request_params = array(
					'message' => $message,
					'user_id' => $data->object->from_id,
					'access_token' => $token,
					'v' => '5.85'
				);
				$get_params = http_build_query($request_params);
				file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
			}
		}
		break;
}
?>
