<?php
require_once("sdata-modules.php");
/**
 * @Author: Eka Syahwan
 * @Date:   2017-12-11 17:01:26
 * @Last Modified by:   Eka Syahwan
 * @Last Modified time: 2018-05-31 19:01:53
*/

$config['bot'] = array(
	'sleep' => 30,
	'cookies' => '', 
);
$config['telegram'] = array(
	'access_token' 	=> '', // access token
	'chat_id' 		=> '',  // message id
);
class ItemkuBOT extends Modules
{
	function __construct($config , $argv){
		$this->config 	= $config;
		$this->argv 	= $argv;
	}
	function save($nama = "" , $data = ""){
		$f = fopen($nama, "a+");
		fwrite($f, $data);
		fclose($f);
	}
	function telegram_spawMenu(){
		$keyboard = array(
		    'keyboard' => array(
		        array("/menu")
		    )
		);
		$encodedKeyboard = json_encode($keyboard);
		$parameters = array(
		    'chat_id' 		=> $this->config['telegram']['chat_id'], 
		    'text' 			=> 'Hai '.$this->nama.', Menu sudah di tempilkan.', 
		    'reply_markup' 	=> $encodedKeyboard
		);
		$head[] = array(
			'post' => $parameters, 
		);
		$url[] = array('url' => 'https://api.telegram.org/'.$this->config['telegram']['access_token'].'/sendMessage');
		$respons = $this->sdata($url , $head);
		$respons = json_decode($respons[0][respons],true);
		if($respons[ok]){
			echo "[+][INFO] ".$this->nama." keyboard menu sudah di tampilkan.\r\n";
		}else{
			echo "[+][INFO] ".$this->nama." keyboard menu belum di tampilkan.\r\n";
		}
	}
	function telegram_spawButtom(){
		$keyboard = [
		    'inline_keyboard' => [
		        [
		            ['text' => 'Cek Saldo Toko', 		'callback_data' => 'ceksaldotoko'],
		            ['text' => 'Cek Status Toko', 		'callback_data' => 'cekstatustoko'],
		            ['text' => 'Status Toko', 			'callback_data' => 'statustoko'],
		            ['text' => 'Buka/Tutup Toko', 		'callback_data' => 'bukatutuptoko']
		        ]
		    ]
		];
		$encodedKeyboard = json_encode($keyboard);
		$parameters = array(
		    'chat_id' 		=> $this->config['telegram']['chat_id'], 
		    'text' 			=> 'Hai '.$this->nama, 
		    'reply_markup' 	=> $encodedKeyboard
		);
		$head[] = array(
			'post' => $parameters, 
		);
		$url[] = array('url' => 'https://api.telegram.org/'.$this->config['telegram']['access_token'].'/sendMessage');
		$respons = $this->sdata($url , $head);
		$respons = json_decode($respons[0][respons],true);
		if($respons[ok]){
			echo "[+][INFO] ".$this->nama." telah memulai bot.\r\n";
		}else{
			echo "[+][INFO] ".$this->nama." gagal memulai bot.\r\n";
		}
	}
	function telegram_getUpdates(){
		$url[] = array('url' => 'https://api.telegram.org/'.$this->config['telegram']['access_token'].'/getUpdates');
		$respons = $this->sdata($url);
		$respons = json_decode($respons[0]['respons'],true);

		foreach ($respons['result'] as $key => $value) {
			if($value[message][from][first_name]){
				$this->nama = $value[message][from][first_name]." ".$value[message][from][last_name];
			}
			preg_match_all('/'.md5($value['update_id']).'/m', file_get_contents("logs/logs-getupdate.txt") , $matches);
			if(!$matches[0][0]){
				if($value[message][text] == '/start'){
					$this->telegram_spawMenu();
				}
				if($value[message][text] == '/menu'){
					$this->telegram_spawButtom(); 
				}
				$this->save('logs/logs-getupdate.txt' , md5($value[update_id])."\r\n");
			}
			if(!empty($value[callback_query][id]) && !$matches[0][0]){
				$data = array(
					'callback_id' 	=> $value[callback_query][id],
					'update_id' 	=> $value[update_id],
					'message_id' 	=> $value[callback_query][message][message_id],
					'chat_id' 		=> $value[callback_query][message][chat][id],
					'action' 		=> $value[callback_query][data], 
				);
				switch ($data['action']) {
					case 'statustoko':
						$this->telegram_sendMessage('[Status Toko] Sedang tutup.');
					break;
					default:
						# code...
					break;
				}
				$this->save('logs/logs-getupdate.txt' , md5($value[update_id])."\r\n");
			}
		}
	}
	function telegram_sendMessage($text = null){
		$url[] = array(
			'url' => "https://api.telegram.org/".$this->config['telegram']['access_token']."/sendMessage?chat_id=".$this->config['telegram']['chat_id']."&text=".$text, 
		);
		$respons = $this->sdata($url);
		$respons = json_decode($respons[0][respons],true);
		if($respons[ok]){
			echo "[+][INFO] ".$this->nama." telah berhasil mengirimkan pesan.\r\n";
		}else{
			echo "[+][INFO] ".$this->nama." telah gagal mengirimkan pesan.\r\n";
		}
	}
}
$ItemkuBOT = new ItemkuBOT($config , $argv);
$ItemkuBOT->telegram_getUpdates();