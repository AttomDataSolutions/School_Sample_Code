<?php 
	require_once("lib/config.php");
	require_once("lib/api.php");
	
	$school_id = isset($_REQUEST['school_id'])?urlencode($_REQUEST['school_id']):''; //Get the school id
	$schoolDetailData = array(); //Initialize empty array
	$message = ""; //Define empty message variable
	
	//Check the school id is empty or not			
	if(!empty($school_id)){
		
		//SET configuration URL
		$config['api_url']= API_URL;
		$config['api_key']= API_KEY;
		
		$schoolObj = new model($config); //Create class object of library file
		
		//Get the school detail by school id
		$schoolDetails = $schoolObj->getPublicSchoolAddressById($school_id);
		
		//Check that we are getting the school detail result or not
		if ($schoolDetails['status']['code'] == 0) {
			
			//Setting up the variable in pre-set array
		    $SchoolProfileAndDistrictInfo = $schoolDetails['school'][0]['SchoolProfileAndDistrictInfo'];
		
			//School Name
			$schoolDetailData['school']['institutionname'] =  $SchoolProfileAndDistrictInfo['SchoolSummary']['institutionname'];
			
			//Address
			$schoolDetailData['address']['locationaddress'] =  $SchoolProfileAndDistrictInfo['SchoolLocation']['locationaddress'];
			$schoolDetailData['address']['locationcity'] =  $SchoolProfileAndDistrictInfo['SchoolLocation']['locationcity'];
			$schoolDetailData['address']['stateabbrev'] =  $SchoolProfileAndDistrictInfo['SchoolLocation']['stateabbrev'];
			$schoolDetailData['address']['ZIP'] =  $SchoolProfileAndDistrictInfo['SchoolLocation']['ZIP'];
			
			//Contact
			$schoolDetailData['contact']['phone'] =  $SchoolProfileAndDistrictInfo['SchoolContact']['phone'];
			
			//Website
			$schoolDetailData['website']['Websiteurl'] =  $SchoolProfileAndDistrictInfo['SchoolContact']['Websiteurl'];
			
			//Technologymeasuretype
			$schoolDetailData['technology']['Technologymeasuretype'] =  $SchoolProfileAndDistrictInfo['SchoolTech']['Technologymeasuretype'];
			
			//Special Eduction
			$schoolDetailData['eucation']['specialeducation'] =  $SchoolProfileAndDistrictInfo['SchoolDetail']['specialeducation'];
			
			//No of Student
			$schoolDetailData['enrollment']['Studentsnumberof'] =  $SchoolProfileAndDistrictInfo['SchoolEnrollment']['Studentsnumberof'];
			$schoolDetailData['enrollment']['Studentteacher'] =  $SchoolProfileAndDistrictInfo['SchoolEnrollment']['Studentteacher'];
			
			//dates
			$schoolDetailData['dates']['startDate'] =  $SchoolProfileAndDistrictInfo['DistrictSummary']['startDate'];
			$schoolDetailData['dates']['endDate'] =  $SchoolProfileAndDistrictInfo['DistrictSummary']['endDate'];
			
			//Principle Name
			$schoolDetailData['principle']['Fullname'] =  $SchoolProfileAndDistrictInfo['DistrictContact']['Prefixliteral']." ".$SchoolProfileAndDistrictInfo['DistrictContact']['Firstname']." ".$SchoolProfileAndDistrictInfo['DistrictContact']['Lastname'];
			echo $response = '<div class="active box">
				<h1 class="white">'.$schoolDetailData['school']['institutionname'].'</h1>
				<div class="address">
					<img src="images/icons/pointer.png" alt="pointer">
					<div class="location">
						<h3 class="white">Contact</h3>
						<p class="white">'.$schoolDetailData['contact']['phone'].' / '.$schoolDetailData['website']['Websiteurl'].'</p>
					</div>
				</div>
				<table class="table table-responsive">
					<tbody>
						<tr>
							<td class="info one" width="50%">
								<img src="images/icons/robot.png" alt="robot">
								<div class="right">
									<h3 class="white">Technology<br>'.$schoolDetailData['technology']['Technologymeasuretype'].'</h3>
									<img src="images/icons/bars.png" alt="bars">
								</div>
							</td>
							<td class="info two">
								<img src="images/icons/puzzle.png" alt="puzzle">
								<div class="right">
									<h3 class="white">Special<br>Education</h3>
									<span class="data">'.$schoolDetailData['eucation']['specialeducation'].'</span>
								</div>
							</td>
						</tr>
						<tr>
							<td class="info one">
								<img src="images/icons/students.png" alt="students">
								<div class="right">
									<h3 class="white">Number of<br>Students Enrolled</h3>
									<span class="data">'.$schoolDetailData['enrollment']['Studentsnumberof'].'</span>
								</div>
							</td>
							<td class="info two">
								<img src="images/icons/bag.png" alt="bag">
								<div class="right">
									<h3 class="white">Start and<br>End Dates</h3>
									<span class="data-number">'.$schoolDetailData['dates']['startDate'].' - '.$schoolDetailData['dates']['endDate'].'</span>
								</div>
							</td>
						</tr>
						<tr>
							<td class="info three">
								<img src="images/icons/percent.png" alt="percent">
								<div class="right">
									<h3 class="white">Student/Teacher<br>Ratio</h3>
									<span class="data">'.$schoolDetailData['enrollment']['Studentteacher'].'/1</span>
								</div>
							</td>
							<td class="info four">
								<img src="images/icons/card.png" alt="card">
								<div class="right">
									<h3 class="white">Principal<br>Name</h3>
									<span class="data-number">'.$schoolDetailData['principle']['Fullname'].'</span>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>';
			die;
		}else{
			//Set error message when data not recived
			$message = "We are unable to get school detail related to this id, Please try later"; die;
		}
		
	}else{
		//Set error message when school id not recived
		$message = "Please provide the school id";die;
	}
		
	if(!empty($schoolDetailData)){
	
	}else{
		//Set error message when data not recived
		$message = "We are unable to get school detail related to this id, Please try later";die;
	}
?>
