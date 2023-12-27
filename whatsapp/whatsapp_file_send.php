<?php    
	
	//$phone = "9101391850";
	//$customer_name="Mr. Imran Hussain";
	//$reg_id="101/0322";
	
	$customer_name=$pat_name_full;
	$reg_id=$opd_id;
	
	$ch = curl_init();
	$ApiUrl = 'http://api.gupshup.io/sm/api/v1/template/msg';
	$phone_no = "91".$phone;
	$template = array(
		"id"=> "fdd8db06-ebcc-4294-8f5c-6552ec14b156",
		"params"=> [$customer_name,$reg_id]
	);
	$message = array(
		"type"=> "document",
		"document"=> [
			"link"=>"https://penguinhis.in/penguinhis/reports/1999_0321_16_25_49.pdf",
			"filename"=> "Medicity Report"]
	);
	
	$msg = json_encode($msg);
	$template = json_encode($template);
	$message = json_encode($message);
	#917099001974 Is Fixed Number
	$data = 'source=917099001974&destination='.$phone_no.'&template='.$template.'&message='.$message;

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
