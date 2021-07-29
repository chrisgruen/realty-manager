$(document).ready(function () {	
	
	$('.ajax-loading').hide();

	$('.select-city').on('change', function() {	
		var id_city = this.value;
		var url= "/suche?tx_realtymanager_immobilienmanager%5Baction%5D=ajaxselectdistrict&tx_realtymanager_immobilienmanager%5Bcontroller%5D=RealtyManager&type=999999&cityId="+id_city;
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
	
	$('#searchform').on('submit', function(event) {
		event.preventDefault();
		scrollToTop();
		$('.ajax-loading').show();
		$('#ajaxSearchResult').hide();

		var url= "/suche?tx_realtymanager_immobilienmanager%5Baction%5D=ajaxsearch&tx_realtymanager_immobilienmanager%5Bcontroller%5D=RealtyManager&type=999999";
		var resultContainer = $('#ajaxSearchResult');
		var select_district = $('#districts option:selected').val()
		var formData = $('#searchform').serialize();

		$.ajax({
			type: "POST",
			url: url,
			headers: {
			 'Cache-Control': 'no-cache, no-store, must-revalidate',
			 'Pragma': 'no-cache',
			 'Expires': '0'
			},
			dataType: 'html',
			data: formData,
			success: function (content) {
				$('#ajaxSearchResult').show();
				resultContainer.html(content).fadeIn('fast');
				$('.ajax-loading').hide();
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





function getQueryParam(param, defaultValue = undefined) {
	location.search.substr(1)
		.split("&")
		.some(function(item) { // returns first occurence and stops
			return item.split("=")[0] == param && (defaultValue = item.split("=")[1], true)
		})
	return defaultValue
}

function scrollToTop() {
	window.scrollTo({
		top: 100,
		left: 0,
		behavior: 'smooth'
	});
}