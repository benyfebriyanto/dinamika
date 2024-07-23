<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('copyright')) {
	function copyright()
	{
		$project = '2021';
		$tahun = date("Y");
		if ($project == $tahun) {
			$cr = $tahun;
		} else {
			$cr = $project . "-" . $tahun;
		}
		return $cr;
	}
}


if (!function_exists('infocategory')) {
	function infocategory($cat)
	{
		switch ($cat) {
			case "overtime":
				$data = array(
					'title' => 'SPL Lembur Karyawan dan Harian',
					'cat' => 'overtime',
					'text_home' => ''
				);
				break;
			case "extra":
				$data = array(
					'title' => 'Extra Fooding Karyawan dan Harian',
					'cat' => 'extra',
					'text_home' => ''
				);
				break;

			default:
				$data = array(
					'title' => 'PT Gunung Madu Plantations',
				);
		}

		$data['masa'] = copyright();

		return ($data);
	}
}
/*
if ( !function_exists('formattanggalindo') )
{
	function formattanggalindo($tanggal){
		if (is_null($tanggal)) {
			return;
		}
		$exp = explode('-', $tanggal);
		$tgl ="";
		if(isset($exp[0])){ $tgl = "$exp[2]-$exp[1]-$exp[0]"; }
		return $tgl;
	}
}*/

if (!function_exists('format_is_decimal')) {
	function format_is_decimal($number = 0, $digit = null)
	{
		if ($number == 0) {
			return $number;
		}
		if (is_null($digit)) $digit = 2;

		if (is_decimal($number)) {
			$format_angka = number_format($number, $digit, ',', '.');
		} else {
			$format_angka = number_format($number, 0, '', '.');
		}
		return $format_angka;
	}
}

if (!function_exists('is_decimal')) {
	function is_decimal($n)
	{
		$result = preg_match('/^\d+\.\d+$/', $n);
		return $result;
	}
}

if (!function_exists('cek_sesi')) {
	function cek_sesi()
	{
		$CI = get_instance();

		if (!$CI->session->userdata('logged_in')) {
			$arr['pesan'] = 'Sessi Anda sudah berakhir,  Anda diharuskan login kembali';
			$arr['judul'] = "Pemberitahuan";
			$CI->load->view('element/include/alert_session_login', $arr);
		} else {
			$newdata = array('last_activity' => time());
			$CI->session->set_userdata($newdata);
			return $CI->session->userdata('logged_in');
		}
	}
}

if (!function_exists('formattanggalindo')) {
	function formattanggalindo($tanggal)
	{
		if (is_null($tanggal)) {
			return;
		}
		$tgl = date("d-m-Y", strtotime($tanggal));
		return $tgl;
	}
}

if (!function_exists('formattanggalax')) {
	function formattanggalax($tanggal)
	{
		if (is_null($tanggal)) {
			return;
		}
		$tgl = date("m/d/Y", strtotime($tanggal));
		return $tgl;
	}
}

if (!function_exists('formattanggalbulanindo')) {
	function formattanggalbulanindo($tanggal)
	{
		if (is_null($tanggal)) {
			return;
		}
		$tgl = date("d F Y", strtotime($tanggal));
		return $tgl;
	}
}

if (!function_exists('tanggalbulanindo')) {
	function tanggalbulanindo($tanggal)
	{
		if (is_null($tanggal)) {
			return;
		}
		$bulan = array(
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$pecahkan = explode('-', $tanggal);

		// variabel pecahkan 0 = tanggal
		// variabel pecahkan 1 = bulan
		// variabel pecahkan 2 = tahun

		$tgl =  $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
		return $tgl;
	}
}

if (!function_exists('angkaromawi')) {
	function angkaromawi($angka)
	{
		if (is_null($angka)) {
			return;
		}
		// var_dump($angka); exit;
		switch ($angka) {
			case 1:
				$romawi = "I";
				break;
			case 2:
				$romawi = "II";
				break;
			case 3:
				$romawi = "III";
				break;
			case 4:
				$romawi = "IV";
				break;
			case 5:
				$romawi = "V";
				break;
			case 6:
				$romawi = "VI";
				break;
			case 7:
				$romawi = "VII";
				break;
			case 8:
				$romawi = "VIII";
				break;
			case 9:
				$romawi = "IX";
				break;
			case 10:
				$romawi = "X";
				break;
			case 11:
				$romawi = "XI";
				break;
			case 12:
				$romawi = "XII";
				break;
		}

		return $romawi;
	}
}

if (!function_exists('formattanggaljamindo')) {
	function formattanggaljamindo($tanggal)
	{
		if (is_null($tanggal)) {
			return;
		}
		$tgl = date("d M Y, H:i", strtotime($tanggal));
		return $tgl;
	}
}

// add by mYa on Feb 6, 2021
if (!function_exists('formatRupiah')) {
	function formatRupiah($angka)
	{
		$hasil_rupiah = number_format($angka, 2, ',', '.');
		return $hasil_rupiah;
	}
}

if (!function_exists('putinplace')) {
	function putinplace($string = NULL, $put = NULL, $position = 0)
	{
		$x = strlen($string);
		$hasil = intval($x / $position);
		$str = "";
		for ($x = 0; $x <= $hasil; $x++) {
			if ($x != $hasil) {
				$str .= substr($string, $position * $x, $position) . $put;
			} else {
				$str .= substr($string, $position * $x, $position);
			}
		}
		return $str;
	}
}

if (!function_exists('formattglwaktuindo')) {
	function formattglwaktuindo($tanggalwkt)
	{
		if (is_null($tanggalwkt)) {
			return;
		}
		//date_default_timezone_set('Asia/Jakarta');
		$tgl = date("d-m-Y H:i:s", strtotime($tanggalwkt));
		return $tgl;
	}
}

if (!function_exists('formattglwaktuindomenit')) {
	function formattglwaktuindomenit($tanggalwkt)
	{
		if (is_null($tanggalwkt)) {
			return;
		}
		//date_default_timezone_set('Asia/Jakarta');
		$tgl = date("d-m-Y H:i", strtotime($tanggalwkt));
		return $tgl;
	}
}

if (!function_exists('kalkulasilembur')) {
	function kalkulasilembur($awal, $akhir)
	{
		$hasil = 0;
		$awal = date("Y-m-d H:i", strtotime($awal));
		$akhir = date("Y-m-d H:i", strtotime($akhir));
		//print_r($akhir);
		$a = strtotime($akhir);
		$b = strtotime($awal);
		$hasil = (($a - $b) / 60) / 60;

		$step = 0.5;
		//$hasil = Math.floor(a/step)*step;
		$hasil = floor($hasil / $step) * $step;

		return $hasil;
	}
}


if (!function_exists('namahari')) {
	function namahari($tanggalwkt)
	{
		if (is_null($tanggalwkt)) {
			return;
		}
		//date_default_timezone_set('Asia/Jakarta');
		$hari = date("w", strtotime($tanggalwkt));

		switch ($hari) {
			case 0:
				$nama = "Minggu";
				break;
			case 1:
				$nama = "Senin";
				break;
			case 2:
				$nama = "Selasa";
				break;
			case 3:
				$nama = "Rabu";
				break;
			case 4:
				$nama = "Kamis";
				break;
			case 5:
				$nama = "Jum'at";
				break;
			case 6:
				$nama = "Sabtu";
				break;
		}

		return $nama;
	}
}

if (!function_exists('namaharibycode')) {
	function namaharibycode($kode)
	{

		switch ($kode) {
			case 0:
				$nama = "Minggu";
				break;
			case 1:
				$nama = "Senin";
				break;
			case 2:
				$nama = "Selasa";
				break;
			case 3:
				$nama = "Rabu";
				break;
			case 4:
				$nama = "Kamis";
				break;
			case 5:
				$nama = "Jum'at";
				break;
			case 6:
				$nama = "Sabtu";
				break;
		}

		return $nama;
	}
}


if (!function_exists('formattglwaktumysql')) {
	function formattglwaktumysql($tanggalwkt)
	{
		if (is_null($tanggalwkt)) {
			return;
		}
		//date_default_timezone_set('Asia/Jakarta');
		$tgl = date("Y-m-d H:i:sa", strtotime($tanggalwkt));
		return $tgl;
	}
}

if (!function_exists('formattanggalmysql')) {
	function formattanggalmysql($tanggal)
	{
		//24-05-1985 ke 1985-05-24
		if ($tanggal) {
			$thn = substr("$tanggal", 6, 4);
			$bln = substr("$tanggal", 3, 2);
			$tgl = substr("$tanggal", 0, 2);
			$hsl_t = "$thn-$bln-$tgl";
			return ($hsl_t);
		}
	}
}

if (!function_exists('selelisihjam')) {
	function selelisihjam($awal, $akhir)
	{
		$hasil = 0;
		$awal = date("Y-m-d H:i", strtotime($awal));
		$akhir = date("Y-m-d H:i", strtotime($akhir));
		//print_r($akhir);
		$a = strtotime($akhir);
		$b = strtotime($awal);
		$hasil = (($a - $b) / 60) / 60;

		return $hasil;
	}
}


if (!function_exists('sendtelegram')) {
	function sendtelegram($message_text, $clientChatid)
	{
		//untuk chek user yg join chanel
		//https://api.telegram.org/bot1985609904:AAEXp4Qr9xErUZ-BEwKhE5MNHWHaxPofQZk/getUpdates

		$CI = get_instance();
		$secret_token = TELE_TOKEN;
		$url = "https://api.telegram.org/bot" . $secret_token . "/sendMessage?parse_mode=markdown&chat_id=" . $clientChatid;
		$url = $url . "&text=" . urlencode($message_text);

		$CI->curl->ssl(FALSE);
		$data = $CI->curl->simple_get($url);

		if ($data) {
			return $data;
		} else {
			return $CI->curl->error_string;
		}
	}
}

if (!function_exists('sendnotifikasifirebase')) {
	function sendnotifikasifirebase($title, $body, $token)
	{

		$url = "https://fcm.googleapis.com/fcm/send";
		//$token = "your device token";
		$serverKey = FIREBASE_SERVER_KEY;
		//$title = "Notification title";
		//$body = "Hello I am from Your php server";
		//$notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => '1');
		//$arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
		$data = array('title' => $title, 'message' => $body);
		$arrayToSend = array('to' => $token, 'data' => $data);
		$json = json_encode($arrayToSend);
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: key=' . $serverKey;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		//Send the request
		$response = curl_exec($ch);
		//Close request
		if ($response === FALSE) {
			die('FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);
	}
}

if (!function_exists('jenislembur')) {
	function jenislembur($kode)
	{

		switch ($kode) {
			case 1:
				$nama = "Reguler";
				break;
			case 2:
				$nama = "Inisiatif";
				break;
		}

		return $nama;
	}
}

if (!function_exists('tanggalweek')) {
	function tanggalweek($bln, $minggu)
	{

		$data = array();
		$ts = strtotime($bln);

		switch ($minggu) {

			case '1':
				$data['start'] = date('Y-m-01', $ts);
				$data['end'] = date('Y-m-07', $ts);
				break;

			case '2':
				$data['start'] = date('Y-m-08', $ts);
				$data['end'] = date('Y-m-14', $ts);
				break;

			case '3':
				$data['start'] = date('Y-m-15', $ts);
				$data['end'] = date('Y-m-21', $ts);
				break;

			case '4':
				$data['start'] = date('Y-m-22', $ts);
				$data['end'] = date('Y-m-28', $ts);
				break;

			case '5':
				$Date = date('Y-m-28', $ts);
				$data['start'] = date('Y-m-d', strtotime($Date . ' + 1 day'));
				$data['end'] = date('Y-m-d', strtotime($Date . ' + 6 day'));
				break;

			default:
				$data['start'] = date('Y-m-01', $ts);
				$data['end'] =  date('Y-m-t', $ts);
				break;
		}

		return $data;
	}
}


// add by mYa on Feb 18. 2021 [AES ENCRYPTION]
if (!function_exists('aes_encryption')) {
	function aes_encryption($plainText)
	{
		//Define cipher 
		$cipher = "aes-128-cbc";

		//Generate a 256-bit encryption key 
		$encryption_key = AES_KEY;

		// Generate an initialization vector 
		// $iv_size = openssl_cipher_iv_length($cipher); 
		$iv = AES_IV;

		//Data to encrypt 
		$encrypted_data = openssl_encrypt($plainText, $cipher, $encryption_key, 0, $iv);

		return $encrypted_data;
	}
}

if (!function_exists('aes_decryption')) {
	function aes_decryption($cipherText)
	{
		//Define cipher 
		$cipher = "aes-128-cbc";

		//Generate a 256-bit encryption key 
		$encryption_key = AES_KEY;

		// Generate an initialization vector 
		// $iv_size = openssl_cipher_iv_length($cipher); 
		$iv = AES_IV;

		//Data to encrypt 
		$decrypted_data  = openssl_decrypt($cipherText, $cipher, $encryption_key, 0, $iv);

		return $decrypted_data;
	}
}

if (!function_exists('generateRandomString')) {
	function generateRandomString($length)
	{
		$characters = '123456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		return $randomString;
	}
}