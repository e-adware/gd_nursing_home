<?php    
	//$api_key_str="14pkgc36wwp6xxp7gphbf2yq0qgaypn3";

	//$phone = "919101391850";
	//$customer_name="Mr. Imran Hussain";
	//$reg_id="101/0322";
	
	$customer_name=$pat_name_full;
	$reg_id=$opd_id;
	
	$ch = curl_init();
	$ApiUrl = 'http://api.gupshup.io/sm/api/v1/template/msg';
	$phone_no = "91".$phone;
	$template = array(
		"id"=> "d29834aa-6eeb-4813-b911-a13208d0a4e9",
		"params"=> [$customer_name,$reg_id]
	);

	#Example:Dear [Tridip], Thanks for choosing Medicity. Your Registration ID is [1234]   


	$msg = json_encode($msg);
	$template = json_encode($template);
	#917099001974 Is Fixed Number
	$data = 'source=917099001974&destination='.$phone_no.'&template='.$template;

	curl_setopt($ch, CURLOPT_URL, $ApiUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	$headers = array();
	$headers[] = 'Cache-Control: no-cache';
	$headers[] = 'Content-Type: application/x-www-form-urlencoded';
	$headers[] = 'Apikey: 14pkgc36wwp6xxp7gphbf2yq0qgaypn3';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//echo $result = curl_exec($ch);
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		//echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);
	
?>
