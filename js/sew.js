var sew = {
	
	start : function() {
	if ($('.jq_date').length>0){
		$('.jq_date').datepicker({yearRange: "-100:+0"}).attr("readonly", "readonly");
		}
	$('#meeting a').click(function(){
			$('#meeting').html('<p>Wczytuję dane</p>').load("/index.php",{'action':'m_date'}, 
				function (){
					$('#m_date').change(function(){
	    		$('#select_hour').html('Wczytuję dane').load('/index.php',{'action':'m_time','date':$('#m_date').val()})});
	    	});
		return false;
	});
	
	$('.notice_view').click(function(e){
		e.preventDefault();
		$(this).parent().html('<p>Wczytuję dane</p>').load($(this).attr('href')+'&ajax=1').fadeIn(1000);
	});

	$('.notice_change').click(function(e){
		e.preventDefault();
		$(this).parent().parent().parent().parent().parent().html('<p>Wczytuję dane</p>').load($(this).attr('href')+'&ajax=1').fadeIn(1000);
	});
	
	$("a.fancybox").fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	false
	});
	this.register_form_check();
},
	
	notice_create : function(){
		$('#notice_create').click(function(e){
			e.preventDefault();
			if (x==undefined) var x = this;
			if (x.index==undefined)
				x.index = 0;
			else 
				x.index = x.index+1;
			$(this).parent().parent().before('<tr><td id="add_notice_'+x.index+'"></td></tr>');	 
			$('#add_notice_'+x.index).html('<p>Wczytuję dane</p>').load($(x).attr('href')+'&ajax=1').fadeIn(1000);
		});
	},
	
	rank_check : function(){
		window.onbeforeunload = function (evt) {
			if ($('.rank_field').attr('value')=='' || $('.rank_field').attr('value')==0){
				var message = 'Nie wystawiono oceny wolontariuszowi';
				if (typeof evt == 'undefined') {
					evt = window.event;
				}
				if (evt) {
					evt.returnValue = message;
				}
				return message;
			}
		}
	},
	
	ajax_forms : function (){
    var options = { 
        target:        	'',   //@todo FILL 
        beforeSubmit:  	sew.showRequest,  // pre-submit callback 
        success:       	sew.showResponse,    
        type: 					'POST'
    };
		$('form.ajaxForm').ajaxForm(options);
	},

	showRequest: function (formData, jqForm, options) {
		formData.push( { name: "ajax", value: 1 }); 
		sew.global = $(jqForm).parent().html('<p>Wczytuję dane</p>').attr('id');
    return true; 
	},
	
	showResponse : function (responseText, statusText)  { 
		document.getElementById(sew.global).innerHTML = responseText; 
	}, 
	
	notice_form_change : function(){
		forms = $('.ajaxForm.notice');
		$(forms).each(function(){
			$('.type_of',this).each(function (){
				$(this).change(function(){
					sew.notice_change($(this));
				});
			});
		});
	},

	notice_form_load : function(){
		forms = $('.ajaxForm.notice');
		$(forms).each(function(){
			$('.type_of',this).each(function (){
				sew.notice_load($(this));
			});
		});
	},
	
	hider : function (e){
		if($(e).css('display')!='none')
			$(e).fadeOut('slow');
	},

	instant_hider : function (e){
		if($(e).css('display')!='none')
			$(e).css('display','none');
	},
	shower : function (e){//:P
		if($(e).css('display')=='none')
			$(e).fadeIn('slow');
	},
	
	notice_change : function (e){
			var value = $(e).fieldValue();
			var node = $(e).parent().parent().parent();
			switch (value[0]){
			case "spotkanie" :
				$('.m_date', node).each(function(){sew.shower(this)});
				$('.m_presence', node).each(function(){sew.shower(this)});
				$('.rank', node).each(function(){sew.shower(this)});
				$('.text_value', node).each(function(){sew.shower(this)});
				$('.amount', node).each(function(){sew.hider(this)})
				$('.valuables', node).each(function(){sew.hider(this)})
				$('.final_nr', node).each(function(){sew.hider(this)})
				$('.ident_nr', node).each(function(){sew.hider(this)})
				break;
			case 'rozliczenie' :
				$('.m_date', node).each(function(){sew.hider(this)});
				$('.m_presence', node).each(function(){sew.hider(this)});
				$('.rank', node).each(function(){sew.hider(this)});
				$('.amount', node).each(function(){sew.shower(this)})
				$('.valuables', node).each(function(){sew.shower(this)})
				$('.final_nr', node).each(function(){sew.shower(this)})
				$('.ident_nr', node).each(function(){sew.shower(this)})
				$('.text_value', node).each(function(){sew.shower(this)});
				break;
			case 'numer identyfikatora' :
				$('.m_date', node).each(function(){sew.hider(this)});
				$('.m_presence', node).each(function(){sew.hider(this)});
				$('.rank', node).each(function(){sew.hider(this)});
				$('.amount', node).each(function(){sew.hider(this)})
				$('.valuables', node).each(function(){sew.hider(this)})
				$('.final_nr', node).each(function(){sew.shower(this)})
				$('.ident_nr', node).each(function(){sew.shower(this)})
				$('.text_value', node).each(function(){sew.shower(this)});
				break;
			case 'policja' :
			case 'nagroda' :
			case 'inne' :
				$('.m_date', node).each(function(){sew.hider(this)});
				$('.m_presence', node).each(function(){sew.hider(this)});
				$('.rank', node).each(function(){sew.hider(this)});
				$('.amount', node).each(function(){sew.hider(this)})
				$('.valuables', node).each(function(){sew.hider(this)})
				$('.final_nr', node).each(function(){sew.hider(this)})
				$('.ident_nr', node).each(function(){sew.hider(this)})
				$('.text_value', node).each(function(){sew.shower(this)});
				break;
			}
	},
	
	notice_load : function (e){
		
		var value = $(e).fieldValue();
		var node = $(e).parent().parent().parent();
		switch (value[0]){
		case 'spotkanie' :
			$('.m_date', node).each(function(){sew.shower(this)});
			$('.m_presence', node).each(function(){sew.shower(this)});
			$('.rank', node).each(function(){sew.shower(this)});
			$('.text_value', node).each(function(){sew.shower(this)});
			$('.amount', node).each(function(){sew.instant_hider(this)})
			$('.valuables', node).each(function(){sew.instant_hider(this)})
			$('.final_nr', node).each(function(){sew.instant_hider(this)})
			$('.ident_nr', node).each(function(){sew.instant_hider(this)})
			break;
		case 'rozliczenie' :
			$('.m_date', node).each(function(){sew.instant_hider(this)});
			$('.m_presence', node).each(function(){sew.instant_hider(this)});
			$('.rank', node).each(function(){sew.instant_hider(this)});
			$('.amount', node).each(function(){sew.shower(this)})
			$('.valuables', node).each(function(){sew.shower(this)})
			$('.final_nr', node).each(function(){sew.shower(this)})
			$('.ident_nr', node).each(function(){sew.shower(this)})
			$('.text_value', node).each(function(){sew.shower(this)});
			break;
		case 'numer identyfikatora' :
			$('.m_date', node).each(function(){sew.hider(this)});
			$('.m_presence', node).each(function(){sew.hider(this)});
			$('.rank', node).each(function(){sew.hider(this)});
			$('.amount', node).each(function(){sew.hider(this)})
			$('.valuables', node).each(function(){sew.hider(this)})
			$('.final_nr', node).each(function(){sew.shower(this)})
			$('.ident_nr', node).each(function(){sew.shower(this)})
			$('.text_value', node).each(function(){sew.shower(this)});
			break;
		case 'policja' :
		case 'nagroda' :
		case 'inne' :
			$('.m_date', node).each(function(){sew.instant_hider(this)});
			$('.m_presence', node).each(function(){sew.instant_hider(this)});
			$('.rank', node).each(function(){sew.instant_hider(this)});
			$('.amount', node).each(function(){sew.instant_hider(this)})
			$('.valuables', node).each(function(){sew.instant_hider(this)})
			$('.final_nr', node).each(function(){sew.instant_hider(this)})
			$('.ident_nr', node).each(function(){sew.instant_hider(this)})
			$('.text_value', node).each(function(){sew.shower(this)});
			break;
		}
	},
	photo_check : function(){
		if (($('input[type=file]').val()=='' && $('.vphoto').length ==0)){
			return confirm('Nie zostało dodane zdjęcie. Czy jesteś pewien/a że chcesz kontynuować?');
		}
		return true;
	},
	
	register_form_check : function(){
		var T = this;
		$('#register').submit(function(e){
			if (!T.photo_check()){
				e.preventDefault();
			}
		});
	}
}



$(document).ready(function(){
	sew.start();
	sew.ajax_forms();
	sew.notice_create();
});

$(document).ajaxComplete(function(request, settings){
	sew.start();
	sew.ajax_forms();
	sew.notice_form_change();
	sew.notice_form_load();
	sew.rank_check();
});
