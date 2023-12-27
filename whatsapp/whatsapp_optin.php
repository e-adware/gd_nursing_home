<?php    
	//$api_key_str="14pkgc36wwp6xxp7gphbf2yq0qgaypn3";

	//$phone = "9101391850";
	
	$ch_opt = curl_init();
    $ApiUrl_Opt = 'https://api.gupshup.io/sm/api/v1/app/opt/in/Medicity';
    $dataOpt ='user=91'.$phone;
    
    curl_setopt($ch_opt, CURLOPT_URL, $ApiUrl_Opt);
    curl_setopt($ch_opt, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch_opt, CURLOPT_POST, 1);
    curl_setopt($ch_opt, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch_opt, CURLOPT_POSTFIELDS, $dataOpt);
    
    $headers_opt = array();
    $headers_opt[] = 'Cache-Control: no-cache';
    $headers_opt[] = 'Content-Type: application/x-www-form-urlencoded';
    $headers_opt[] = 'Apikey: 14pkgc36wwp6xxp7gphbf2yq0qgaypn3';
    curl_setopt($ch_opt, CURLOPT_HTTPHEADER, $headers_opt);
    
    $result = curl_exec($ch_opt);
    curl_close($ch_opt);
?>
