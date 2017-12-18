/*Start Script is written for display page loader at the time of load the page*/

	document.onreadystatechange = function () {
	  var state = document.readyState
	  if (state == 'interactive') {
		   document.getElementById('body').style.visibility="hidden";
		   document.getElementById('header').style.visibility="hidden";
	  } else if (state == 'complete') {
		  setTimeout(function(){
			 document.getElementById('interactive');
			 document.getElementById('load').style.visibility="hidden";
			 document.getElementById('body').style.visibility="visible";
			 document.getElementById('header').style.visibility="visible";
			$("#1").trigger("click");
		  },1000);
	  }
	}

	/*Start Script for display results in slider*/
	var swiper = new Swiper('.swiper-container', {
		slidesPerView: 3,
		spaceBetween: 30,
		pagination: {
			el: '.swiper-pagination',
			clickable: true,
		},
		navigation: {
			nextEl: '.swiper-button-next',
			prevEl: '.swiper-button-prev',
		},
		
		// Responsive breakpoints
		breakpoints: {
        // when window width is <= 640px
			992: {
			  slidesPerView: 1,
			  spaceBetween: 30
			}
		}
    });

	/*Perform Jquery operation when page is ready*/
	$(function(){
		
		//Calculate screen height dynamically and set the min height accordingly through below script	
		setTimeout(function(){$('#body').css("min-height",(parseInt($( window ).height())-55)+"px");},500);    
		
		//Get the selected school details using Ajax when someone click on slider item
		$("body").on("click",".selectSchool",function(){
			
			var schoolID = $(this).data("school"); //Get Selected school id
			var blockID = $(this).attr("id"); //Get Selected item/block id
			
			//Selection code
			$(".whitebg").show(); 
			$(".greybg").hide();
			$(".selectSchool").removeClass("active");
			$(".colorCode").removeClass("white");
			
			$(this).find(".whitebg").hide();
			$(this).find(".greybg").show();
			$(this).addClass("active");
			$(this).find('.colorCode').addClass("white");
			
			//Selection code End
			$.ajax({
				url: 'school-details.php?school_id='+schoolID,//AJAX URL WHERE THE LOGIC HAS BUILD
				beforeSend: function() {
					//Show ajax loader untill get the response 	
					$('.ajax-loader').show();
				
				},
				success:function(response) {
					
					$('.ajax-loader').hide();//Hide ajax loader after get the response 	
					$(".schoolDetail").html(response);	//Display ajax response on school detail div										
					var focusElement = $(".schoolDetail"); //Take the focus of the user on school detail block through slide snimation
					ScrollToTop(focusElement, function() { focusElement.focus(); }); 
					
					setTimeout(function(){ openInfoModal(parseFloat(blockID)); },100); //Open map info window for selected schools
				}
			});
		});
		
	});
			
	/*Search School Script*/
	$('#getEstimate').click(function(){
		
		var org_val = $("#getEstimate").val();//MAKE THE BUTTON FADE AFTER CLICKED ON IT
		$("#getEstimate").val('Wait...');//MAKE THE BUTTON FADE AFTER CLICKED ON IT
		$("#getEstimate").attr('disabled',true);//MAKE THE BUTTON FADE AFTER CLICKED ON IT
		$("#ajaxLoad").show();//SHOW THE LOADER WHEN AJAX REQUESTED
		$('#innerCont').addClass('overlayBg');
		
		var search = $('#search').val();
		var checkValidate = true;	
		var message = '';	
		$("span.customerror").html(message);
		
		$("#estimateForm input[required=true]").each(function(){
			$(this).css('border-color',''); 
			if(!$.trim($(this).val())){ //if this field is empty 
				$(this).css('border-color','red'); //change border color to red   
				checkValidate = false; //set do not proceed flag
				message = 'This field is required.';				
				$("span.customerror").html(message);
			}
		});	
			
		if(checkValidate && search!=''){
			var search = search.replace("#",",");
			var explodeAddr = search.split(',');
			
			var add2Str = '';
			for(i=0;i<explodeAddr.length;i++){
				if(i==0){continue;}
				if(i==1){add2Str+=$.trim(explodeAddr[i]);continue;}
				add2Str+= ','+explodeAddr[i];
			}
			
			var pCode = $('#postal_code').val();
				
			var urlToExecute = 'results.php?address1='+explodeAddr[0]+'&address2='+add2Str;
			
			//reset previously set border colors and hide all message on .keyup()
			$("#estimateForm  input[required=true]").keyup(function() { 
				$(this).css('border-color',''); 
				//$("#result").slideUp();
			});
			$("#getEstimate").val(org_val);//MAKE THE BUTTON FADE AFTER CLICKED ON IT
			$("#getEstimate").attr('disabled',false);	
			window.location.href = urlToExecute;
		}
		$("#getEstimate").val(org_val);//MAKE THE BUTTON FADE AFTER CLICKED ON IT
		$("#getEstimate").attr('disabled',false);
		$("#ajaxLoad").hide();//HIDE THE LOADER WHEN AJAX REQUESTED
		$('#innerCont').removeClass('overlayBg');
		
	});
	/*Search School Script*/
	
	/* Auto google location suggestion */
	var placeSearch, autocomplete;
	var componentForm = {
		postal_code: 'short_name'
	};

	function initAutocomplete() {
		autocomplete = new google.maps.places.Autocomplete(
			/** @type {!HTMLInputElement} */(document.getElementById('search')),
			{types: ['geocode']});
		autocomplete.addListener('place_changed', fillInAddress);
	}

	function fillInAddress() {
		hideLabel();
		// Get the place details from the autocomplete object.
		var place = autocomplete.getPlace();

		for (var component in componentForm) {
		  document.getElementById(component).value = '';
		  document.getElementById(component).disabled = false;
		}
		console.log(place);
		// Get each component of the address from the place details
		// and fill the corresponding field on the form.
		for (var i = 0; i < place.address_components.length; i++) {
		  var addressType = place.address_components[i].types[0];
		  if (componentForm[addressType]) {
			var val = place.address_components[i][componentForm[addressType]];
			
			document.getElementById(addressType).value = val;
		  }
		}
		
		document.getElementById('search').value = document.getElementById('search').value + ' '+document.getElementById('postal_code').value;
	}

	function hideLabel(){
			var message = '';	
			$("span.customerror").html(message);
		}

	// Bias the autocomplete object to the user's geographical location,
	// as supplied by the browser's 'navigator.geolocation' object.
	function geolocate() {
		if (navigator.geolocation) {
		  navigator.geolocation.getCurrentPosition(function(position) {
			var geolocation = {
			  lat: position.coords.latitude,
			  lng: position.coords.longitude
			};
			var circle = new google.maps.Circle({
			  center: geolocation,
			  radius: position.coords.accuracy
			});
			autocomplete.setBounds(circle.getBounds());
		  });
		}
	}
	
	function ScrollToTop(el, callback) { 
		$('html, body').animate({ scrollTop: $(el).offset().top - 50 }, 'slow', callback);
	} 
	
	/*Call search function when someone cick on enter key*/
	$('#search').keypress(function (e) {
		var key = e.which;
		if(key == 13)  // the enter key code
		{
			$('#getEstimate').click();
			return false;  
		}
	});   
	/* Auto google location suggestion */