<?php 
class model
{
    private $obapiurl, $obapikey; //Setup private variables

    function __construct($config)
    {
        if($config["api_key"]=="" || $config["api_key"]=="COPY_YOUR_API_KEY_HERE"){
			die("Please paste your API KEY in config.php which is located under library folder.");
		}
        $this->obapiurl = $config["api_url"]; //Get api url while creating object
		$this->obapikey = $config["api_key"]; //Get api key while creating object
		
	}
	
	private function curlSchoolAPI($url, $apiKey = null){
		
		$curl = curl_init(); //cURL initialization
		
		//Set cURL array with require params
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"apikey: " . ($apiKey!=''?$apiKey:$this->obapikey)
			)
		));
      
		$response = curl_exec($curl);
		$err = curl_error($curl);
		//echo "<pre>"; print_r($err); die;
		curl_close($curl);
	 
		if ($err) {
			return '{"status": { "code": 999, "msg": "cURL Error #:" . $err."}}';
		}else{
			return json_decode($response, true);
		}
	}
    
	function getPublicSchoolAddressById($schoolID){
		
		$url = $this->obapiurl ."/propertyapi/v1.0.0/school/detail?id=".$schoolID;
		return $this->curlSchoolAPI($url); 
	}
	
	public function getSchoolSampleCode($add1=null,$add2=null){
		$url = $this->obapiurl ."/propertyapi/v1.0.0/property/detailwithschools?address1=$add1&address2=$add2";
		return $this->curlSchoolAPI($url);  
	}
	
	public function getSchoolSamplePrivateCode($lat,$long){
		$url = $this->obapiurl ."/propertyapi/v1.0.0/school/snapshot?latitude=$lat&longitude=$long&radius=10&filetypetext=private";
		return $this->curlSchoolAPI($url);  
	}
}	
?>
