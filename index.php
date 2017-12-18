<!DOCTYPE html>
<html class="no-js" lang="">

	<head>
		<!-- All meta tags define below -->
		<meta charset="utf-8">
		<meta name="keywords" content="Sample School Code" >
		<meta name="description" content="Display public and private school listing bases on property search">
		<meta name="author" content="Kevin">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
		
		<!-- Title of the page -->
		<title>School Area</title>
		
		<link rel="apple-touch-icon" href="apple-touch-icon.png">
		<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
		
		<!-- CDN call for CSS Start-->
		<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,600,700|Roboto:300,400" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.6/css/swiper.min.css" />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
		<!-- CDN call for CSS end -->
		

		<!-- css/main.css -->
		<link rel="stylesheet" href="css/main.css">
		<!-- endbuild -->
		
	</head>
	<!-- This div is used for display page loader untill full page is load -->
	<div id="load"></div>

	<body id="innerCont" class="home">
		
		<!-- Display loader image whe page will start loading start-->
		<div style="display:none" class="ajax-loader">
		  <img src="images/35.gif" class="img-responsive" />
		</div>	
		<!-- Display loader image whe page will start loading end-->
		
		<?php 
			require_once("lib/config.php");
			require_once("lib/api.php");
			
			//Implement api call logic once we get address from google location
			
			if ($_SERVER['REQUEST_METHOD'] == 'GET'){
				//SET configuration URL
				$config['api_url']= API_URL;
				$config['api_key']= API_KEY;
				if($config["api_key"]=="" || $config["api_key"]=="COPY_YOUR_API_KEY_HERE"){
					die("Please paste your API KEY in config.php which is located under library folder.");
				}
				$schoolObj = new model($config); //Create class object of library file
				
				$add1 = '4529 Winona Court';
				$add2 = 'Denver, CO 80212, United States';
				$address1 = isset($_REQUEST['address1'])?urlencode($_REQUEST['address1']):urlencode($add1);
				$address2 = isset($_REQUEST['address2'])?urlencode($_REQUEST['address2']):urlencode($add2);
				
				if(!empty($address1) && !empty($address1)){
					
					//GET LATITUDE LONGITUDE FROM SELECTED ZIP CODE
					$url = "https://maps.google.com/maps/api/geocode/json?address=".$address1.$address2."&sensor=false"; 
								
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					$response = curl_exec($ch);
					curl_close($ch);
					$response_a = json_decode($response);
					
					if(@$response_a->status=='ZERO_RESULTS'){
						//SET LAT LONG AS PER IP ADDRESS
						$sourceLocationLatitude = '40.330837';
						$sourceLocationLongitude = '-79.960191';
					}else{
						@$sourceLocationLatitude = $response_a->results[0]->geometry->location->lat;
						@$sourceLocationLongitude = $response_a->results[0]->geometry->location->lng;
					}
					
				}
				
				$allPrivateSchools = $schoolObj->getSchoolSamplePrivateCode($sourceLocationLatitude,$sourceLocationLongitude);
				$psArray=array();
				if ($allPrivateSchools['status']['code'] == 0) {
					
					foreach($allPrivateSchools['school'] as $pvt_s=>$private_school){
						$psArray[$pvt_s]['OBInstID'] = $private_school['Identifier']['OBInstID'];
						$psArray[$pvt_s]['InstitutionName'] = $private_school['School']['InstitutionName'];
						$psArray[$pvt_s]['GSTestRating'] = $private_school['School']['GSTestRating'];
						$psArray[$pvt_s]['gradelevel1lotext'] = $private_school['School']['gradelevel1lotext'];
						$psArray[$pvt_s]['gradelevel1hitext'] = $private_school['School']['gradelevel1hitext'];
						$psArray[$pvt_s]['Filetypetext'] = $private_school['School']['Filetypetext'];
						$psArray[$pvt_s]['geocodinglatitude'] = $private_school['School']['geocodinglatitude'];
						$psArray[$pvt_s]['geocodinglongitude'] = $private_school['School']['geocodinglongitude'];
						$psArray[$pvt_s]['distance'] = $private_school['School']['distance'];
					}
				}
				
				$allPublicSchools = $schoolObj->getSchoolSampleCode($address1,$address2);
				
				if ($allPublicSchools['status']['code'] == 0) {
					if(!empty($allPublicSchools['property'][0]["school"])){
						
						if(!empty($psArray)){
							
							$final_array = array_merge($allPublicSchools['property'][0]["school"],$psArray);
						}else{
							$final_array = $allPublicSchools['property'][0]["school"];
						}
						
						foreach($final_array as $k=>$schoolVal){
							if(!isset($schoolVal['OBInstID'])){
								continue;
							}
							$schoolDetails = $schoolObj->getPublicSchoolAddressById($schoolVal['OBInstID']);
							if ($schoolDetails['status']['code'] == 0) {
								$final_array[$k]['school_address']['locationaddress'] =  $schoolDetails['school'][0]['SchoolProfileAndDistrictInfo']['SchoolLocation']['locationaddress'];
								$final_array[$k]['school_address']['locationcity'] =  $schoolDetails['school'][0]['SchoolProfileAndDistrictInfo']['SchoolLocation']['locationcity'];
								$final_array[$k]['school_address']['stateabbrev'] =  $schoolDetails['school'][0]['SchoolProfileAndDistrictInfo']['SchoolLocation']['stateabbrev'];
								$final_array[$k]['school_address']['ZIP'] =  $schoolDetails['school'][0]['SchoolProfileAndDistrictInfo']['SchoolLocation']['ZIP'];
								
							}else{
								$final_array[$k]['school_address']['locationaddress'] = '';
								$final_array[$k]['school_address']['locationcity'] = '';
								$final_array[$k]['school_address']['stateabbrev'] = '';
								$final_array[$k]['school_address']['ZIP'] = '';
							}
							
						}
					}
				} 
				//	echo "<pre>";print_r($final_array); die;
				
				
			}
			//echo "<pre>"; print_r($allPublicSchools['property'][0]["school"]);die;
		?>
			<!-- header -->
	
			<div style="visibility:hidden" id="header">
				<span>
					Schools in the area
				</span>
			</div>
			<!-- /.header -->

			<!-- body -->
			<div style="visibility:hidden"  id="body">
				<div class="container">
					<div class="row">
						<div class="col-md-8 col-md-offset-4 col-xs-12">
							<div class="search" id="estimateForm">
								<!-- NEW CODE -->
								<input class="form-control" type="text" placeholder="Property Search" value="<?php echo urldecode($address1)." ".urldecode($address2); ?>" id="search" onFocus="geolocate()" required="true">
								<input type="hidden" name="access_token" id="access_token" value="<?php echo $customerId; ?>">
								<input type="hidden" name="postal_code" id="postal_code" value="">
								<input class="btn btn-primary" id="getEstimate" type="button" value="Search">
								<em class="info">Ex: 4529 Winona Court, Denver, CO 80212</em>  
								<span class="customerror"></span>
								<!-- NEW CODE -->
							</div>
						</div>
						<div class="slider">
						  <!-- Add Arrows -->
						  <div class="swiper-button-next"><img src="images/icons/right.png" alt="right"></div>
						  <div class="swiper-button-prev"><img src="images/icons/left.png" alt="left"></div>
						  <!-- Swiper -->
						  <div class="swiper-container col-xs-12 mt-30">
							<div class="swiper-wrapper">
								<!-- Slides Loop Start -->
								<?php 
									if(!empty($final_array)) { 
										$i=1;
										foreach($final_array as $publicSchool){
											if(@$publicSchool['InstitutionName'] !=''){ ?>							
										<div class="swiper-slide">
											
											<div data-school="<?php echo $publicSchool['OBInstID']; ?>" id="<?php echo $i; ?>" class="selectSchool box">
												<h1 class="colorCode"><?php echo $publicSchool['InstitutionName']; ?></h1>
												<div class="address">
													<img class="greybg" src="images/icons/pin.png" alt="pin">
													<img class="whitebg" src="images/icons/pin_b.png" alt="pin">
													<div class="location">
														<h3 class="colorCode">Address</h3>
														<p class="colorCode"><?php echo $publicSchool['school_address']['locationaddress']; ?> <?php echo $publicSchool['school_address']['locationcity']; ?><?php echo $publicSchool['school_address']['stateabbrev']; ?>, <?php echo $publicSchool['school_address']['ZIP']; ?></p>
													</div>
												</div>
												<table class="table table-responsive">
													<tbody>
														<tr>
															<td class="info one" width="50%">
																<h3 class="colorCode">Public vs. Private</h3>
																<div class="row">
																	
																	<?php if($publicSchool['Filetypetext'] =='PUBLIC'){ ?>	
																		<div class="whitebg col-xs-12">
																			<img src="images/icons/institution_b2.png" alt="institution">
																		</div>
																		<div class="greybg col-xs-12">
																			<img src="images/icons/institution.png" alt="institution">
																		</div>
																		
																	<?php }else {?>
																		<div class="whitebg col-xs-12">
																			<img src="images/icons/private_b.png" alt="private">
																		</div>
																		<div class="greybg col-xs-12">
																			<img src="images/icons/private.png" alt="private">
																		</div>
																	<?php } ?>
																</div>
															</td>
															<td class="info two">
																<h3 class="colorCode">Distance from Property</h3>
																<div class="row">
																	<div class="col-xs-6">
																		<img class="greybg" src="images/icons/path.png" alt="path">
																		<img class="whitebg" src="images/icons/path_b.png" alt="path">
																	</div>
																	<div class="col-xs-6 distance">
																		<span class="num"><?php echo $publicSchool['distance']; ?></span>
																		<span class="unit">Miles</span>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td class="info three">
																<h3 class="colorCode">Grade Range</h3>
																<div class="grade">
																	<span class="one"><?php echo str_replace(array("TH GRADE","ND GRADE","PRESCHOOL","KINDERGARTEN","3RD GRADE"),array("","","PS","K","3"),$publicSchool['gradelevel1lotext']); ?></span>
																	<span class="range"></span>
																	<span class="two"><?php echo str_replace(array("TH GRADE","ND GRADE","PRESCHOOL","KINDERGARTEN","3RD GRADE"),array("","","PS","K","3"),$publicSchool['gradelevel1lotext']); ?></span>
																</div>
															</td>
															<td class="info four">
																<h3 class="colorCode">School Rating</h3>
																<div class="rating">
																	<span class="rating-img"><?php echo $publicSchool['GSTestRating']; ?></span> / 5
																</div>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										
									</div>
									<?php $i++; ?>
									<?php } ?>		
									<?php } ?>
								<?php } ?>
								<!-- Slides Loop End -->
							</div>
						  </div>
						</div>
						<div class="col-md-4 col-xs-12 mt-30 schoolDetail activeted">
							
						</div>
						<div class="col-md-8 col-xs-12 mt-30 map">
							 <div id="map"></div>
						</div>
					</div>
				</div>
			</div>
			
			<!-- /.body -->
		<!-- CDN call for JS Start-->	
		<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.6/js/swiper.min.js"></script>
		<!-- js/main.js -->
		<script src="js/main.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCPAVKxutIiPNXJr8UeB2wwSrzrFA3-GuI&libraries=places&callback=initAutocomplete"></script>
		
		<!-- CDN call for JS Start-->
		
		
		<script>
		  var gmarkers = [];
		  function initMap() {
			  var locations = [<?php 
				if(!empty($final_array)) { 
				$ps = 1;
				foreach($final_array as $publicSchool){
					if ($publicSchool['InstitutionName'] !=''){ ?>['<?php echo $publicSchool['InstitutionName']; ?>', '<?php echo $publicSchool['geocodinglatitude']; ?>', '<?php echo $publicSchool['geocodinglongitude']; ?>', '<?php echo $ps; ?>','<div id="iw-container"><div class="iw-title"><?php echo $publicSchool['InstitutionName']; ?></div><div class="iw-content"><p><?php echo $publicSchool['school_address']['locationaddress']." ".$publicSchool['school_address']['locationcity']." ".$publicSchool['school_address']['stateabbrev'].", ".$publicSchool['school_address']['ZIP']; ?></p><p class="distance"><?php echo $publicSchool['distance']; ?> Miles<br></div></div>'],
								<?php $ps++; ?>
							<?php } ?>		
						<?php } ?>
				<?php } ?>
				];

				var map = new google.maps.Map(document.getElementById('map'), {
				  zoom: 12,
				  center: new google.maps.LatLng('<?php echo $sourceLocationLatitude; ?>', '<?php echo $sourceLocationLongitude; ?>'),
				  mapTypeId: google.maps.MapTypeId.ROADMAP
				});

				var infowindow = new google.maps.InfoWindow();

				var marker, i;
				//console.log(locations);
				
				 marker = new google.maps.Marker({
					position: new google.maps.LatLng('<?php echo $sourceLocationLatitude; ?>', '<?php echo $sourceLocationLongitude; ?>'),
					map: map,
					animation: google.maps.Animation.DROP,
					icon: 'images/4.png'
				  });
					
					
				  gmarkers.push(marker);
				
				
				
				for (i = 0; i < locations.length; i++) { 
				  marker = new google.maps.Marker({
					position: new google.maps.LatLng(locations[i][1], locations[i][2]),
					map: map,
					animation: google.maps.Animation.DROP,
					icon: 'images/1.png'
				  });
					
				  google.maps.event.addListener(marker, 'click', (function(marker, i) {
					return function() {
						infowindow.setContent(locations[i][4]); 
						infowindow.open(map, marker);
						for (var sm = 0; sm < gmarkers.length; sm++) {
							if(sm!=0){
								gmarkers[sm].setIcon("images/1.png"); 
							}
						}
						marker.setIcon("images/2.png");
					}
				  })(marker, i)); 
				  gmarkers.push(marker);
				}
		  }
		
		function openInfoModal(i) {
		  google.maps.event.trigger(gmarkers[i], "click");
		}	
		
		$(function(){
			initMap();
		});
		</script>
	
	</body>
</html>
