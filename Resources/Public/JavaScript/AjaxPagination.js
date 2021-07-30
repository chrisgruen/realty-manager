$(document).ready(function () {	
	$('.test-link').on('click', function(e) {	
		e.preventDefault();
		alert('click');
	});
});

function getresult(page) {
	var url= "/suche?tx_realtymanager_immobilienmanager%5Baction%5D=ajaxsearch&tx_realtymanager_immobilienmanager%5Bcontroller%5D=RealtyManager&type=999999&page="+page;
	
	var resultContainer = $('#ajaxSearchResult');
	var select_district = $('#districts option:selected').val()
	
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
				$('#ajaxSearchResult').show();
				resultContainer.html(content).fadeIn('fast');
				$('.ajax-loading').hide();
			}
	   });
}
