var Chart = {
	setup: function(scores) {
		Chart.colors = ['#C45987', '#B2BB1E', '#F3901D'];
		Chart.container = $('#chart');
		Chart.dataset = [
		    {
		    	label: 'a',
		    	data: [[0, 0]],
				total: 0
		    }
		];
		
		for (var i = 0; i < scores.length; i++) {
			Chart.dataset[0].total = scores[i].total;
			Chart.dataset[0].data.push([i + 1, scores[i].correct]);
		}
	
		Chart.draw(true);
	},
	
	draw: function(fill) {
		var html = 	'<div class="chart-container">'+
//					'	<span class="feedback" style="'+ (fill ? 'color:'+ Chart.colors[0] + '; ' : 'visibility:hidden;') +'">'+ Chart.dataset[0].total + '/' + Chart.dataset +'</span>' +
//					'	<span class="break"><!--  --></span>'+
					'	<span class="tick tick-yaxis"><!-- --></span>'+
					'	<span class="count count-yaxis">'+ Chart.dataset[0].total +'</span>'+
					'	<span class="label label-yaxis">Score</span>'+
					'	<div class="chart-placeholder"></div>'+
					'	<div class="chart-legend"></div>'+
					'	<span class="break"><!--  --></span>'+
					'	<span class="label label-xaxis">Attempts</span>'+
					'	<span class="count count-xaxis">'+ (Chart.dataset[0].data.length - 1) +'</span>'+
					'	<span class="tick tick-xaxis"><!-- --></span>'+
					'	<span class="break"><!--  --></span>'+
					'</div>';
		
		Chart.container.html(html);
		var placeHolder = Chart.container.find('.chart-placeholder');
		
		$.plot(placeHolder, Chart.dataset, {
	       colors: Chart.colors,
	       series: {
	      	 shadowSize: 0,
	           lines: {
	          	 show: true,
	          	 fill: fill
	           },
	           points: {
	        	   show: false
	           }
	       },
	       xaxis: {
	           ticks: 5,
	           tickDecimals: 0,
	           max: Chart.dataset[0].data.length - 1,
	           show: true,
			   autoscaleMargin: 1
	       },
	       yaxis: {
	           ticks: 5,
	           tickDecimals: 0,
	           max: Chart.dataset[0].total,
	           labelWidth: 10,
	           show: true,
			   autoscaleMargin: 1
	       },/*
	       grid: {
	           backgroundColor: { colors: ["transparent", "transparent"] },
	           borderWidth: 1,
			   borderColor: '#CCC',
	           minBorderMargin: 0
	       },*/
	       legend: {
	    	   show: false
	       }
	    });
	}
}


$(document).ready(function() {			
        $('.info-field').live('mouseenter', function() {
                $(this).find('.info-field-layer').show();
        });

        $('.info-field').live('mouseleave', function() {
                $(this).find('.info-field-layer').hide();
        });
	
	$(".task-button").live("click",function(){
		var _this = $(this);
		var taskName = $(this).attr("rel");
		if ($("#"+taskName).css("display")=="none"){
			$(".description-tasks").hide();
			$(".task-button").find("span").html("+");
			$("#"+taskName).show();
			_this.find("span").html("-");
		} else { 
			$("#"+taskName).hide();
			_this.find("span").html("+");
		}	
	});
	
	$(".select-country").change(function(){
		var idCountry = $(this).val();
		window.location = BASE_EVALUATION_URL + '/' + idCountry;
	});
	
});


