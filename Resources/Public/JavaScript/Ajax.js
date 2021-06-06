

$(document).ready(function () {
	
	
	$('.select-city').on('change', function() {	
		var id_city = this.value;
		url= "/suche?tx_realtymanager_immobilienmanager%5Baction%5D=ajaxselectdistrict&tx_realtymanager_immobilienmanager%5Bcontroller%5D=RealtyManager&type=999999&cityId="+id_city;
		var resultContainer = $('#ajaxSelectDistrictResult');
			
		$.ajax({
		      type: "POST",
		      url: url,
		      headers: {
		         'Cache-Control': 'no-cache, no-store, must-revalidate',
		         'Pragma': 'no-cache',
		         'Expires': '0'
		      },
		      dataType: "html",
		      success: function (content) {
		      	resultContainer.html(content).fadeIn('fast');
		      }
		   });
	});
});

$('.test-ajax').on('click', function(){

   if($(this).data('format')){
      format = $(this).data('format');
   }
 
   var resultContainer = $('#ajaxCallResult');
   data = {"test": "test"};
   url= "/suche?tx_realtymanager_immobilienmanager%5Baction%5D=ajaxcall&tx_realtymanager_immobilienmanager%5Bcontroller%5D=RealtyManager&type=999999";
   
   jQuery.ajax({
	      type: "POST",
	      url: url,
	      headers: {
	         'Cache-Control': 'no-cache, no-store, must-revalidate',
	         'Pragma': 'no-cache',
	         'Expires': '0'
	      },
	      dataType: "html",
	      data: data,
	      success: function (content) {
	    	alert('jquery result');
	      	console.log(content);
	      	resultContainer.html(content).fadeIn('fast');
	      }
	   });
  
});