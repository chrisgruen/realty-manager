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
	
	$('#searchform').submit(function (event) {
		event.preventDefault();
		$('.ajax-loading').show();
		$('#ajaxSearchResult').hide();


		var url= "/suche?tx_realtymanager_immobilienmanager%5Baction%5D=ajaxsearch&tx_realtymanager_immobilienmanager%5Bcontroller%5D=RealtyManager&type=999999";
		var formData = $('#searchform').serialize();
		var resultContainer = $('#ajaxSearchResult');
		
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
			    scrollToTop();
		      }
	   });
	});

	$('#ajaxSearchResult').on('click', '.ajax-widget-paginator a', function (e) {
		e.preventDefault();

		var ajaxQuery = $(this).attr("href");

		var current_page = 0;
		var params = ajaxQuery.split(/\?|\&/);

		params.forEach( function(it) {
			if (it) {
				var param = it.split("=");

				if (param[0] == 'tx_realtymanager_immobilienmanager%5B%40widget_0%5D%5BcurrentPage%5D') {
					current_page = param[1];
				}
				console.log(current_page);
			}
		});

		console.log(current_page);

		if (ajaxQuery  !== undefined && ajaxQuery  !== '') {
			var container = 'news-container-' + $(this).data('container');
			$.ajax({
				url: ajaxQuery,
				type: 'GET',
				success: function (result) {
					console.log(ajaxQuery);
				}
			});
		}


		//alert(ajaxQuery);

		/*
        if (ajaxUrl !== undefined && ajaxUrl !== '') {
            e.preventDefault();
            var container = 'news-container-' + $(this).data('container');
            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                success: function (result) {
                    var ajaxDom = $(result).find('#' + container);
                    $('#' + container).replaceWith(ajaxDom);
                }
            });
        }
        */
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
		top: 200,
		left: 0,
		behavior: 'smooth'
	});
}