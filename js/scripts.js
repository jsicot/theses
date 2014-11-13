var jsHost = (("https:" == document.location.protocol) ? "https://" : "http://");
var opacurl = 'catalogue.bu.univ-rennes2.fr';
var OPAC_SVC =  opacurl +"/r2microws/";
$(document).ready(function() {
	init();
});

function init(){
	getRecord();
}

//--------------------------
//  FUNCTIONS
//--------------------------
function getRecord() {
	if ($('#documents').size() > 0) {
		$('.document').each(function() {
			var num = $(this).attr('id');	
			if(jQuery('.nnt').size() > 0) {
				var d = $(this); 
				var nnt = $(this).children().children('.nnt').text();
				if(nnt){
					var url = 'include/getPPNbyNNT.php?nnt='+nnt+'&callback=?' ;
					$.ajax({
						url : url,
						dataType : 'jsonp',
						success : function(data){
							if(data && data.results){
								
								console.log(data.results.nnt);
								$.each(data.results.ppn, function(p,ppn){
										console.log(ppn);
									if(ppn){
										var url = jsHost+OPAC_SVC+'json.getSru.php?index=dc.identifier&q='+ppn+'&callback=?' ;
										$.ajax({
											url : url,
											dataType : 'jsonp',
											success : function(data){
												if(data && data.record){
													$.each(data.record, function(r,record){
														var biblionumber = record.biblionumber;
														if(record && record.links) {
															$.each(record.links, function(l,links){
																if (!/theses\.fr/.test(links.url) && links.url !== "") {
																	$('.index_title.titre_'+num+' a').attr('href', links.url);
																	$('.index_title.titre_'+num+' a').attr('title', links.label);
																	if($('.index-document-functions.acces_'+num+' span.intra').size() == '0' && $('.index-document-functions.acces_'+num+' span.online').size() == '0'){
																		if($('.index-document-functions.acces_'+num+' .spinner').size() > '0'){
																			$('.index-document-functions.acces_'+num+' .spinner').remove();
																		}
																		$('.index-document-functions.acces_'+num).append('<div class="availability"><span class="label label-warning intra"><a href="'+links.url+'" target="_blank" title="En ligne après authentification">Intranet</span> Accès après authentification</a><br />');
																	}
																	else {
																		if($('.index-document-functions.acces_'+num+' .spinner').size() > '0'){
																			$('.index-document-functions.acces_'+num+' .spinner').remove();
																		}	
																	}
																}
															});
														}
														else if(record && record.item){
															var divItems = jQuery('<div class="items"></div>').attr('class', "panel-collapse collapse");
															var divItems =jQuery(divItems).attr('id','collapseItems'+ biblionumber);
															$.each(record.item, function(i,item){
																if(item.homebranch != "Bibliothèque en ligne"){
																	$('.index_title.titre_'+num+' a').attr('href', '/cgi-bin/koha/opac-detail.pl?biblionumber='+biblionumber);
																	if($('.index-document-functions.acces_'+num+' span.onloan').size() == '0' ){
																		if($('.index-document-functions.acces_'+num+' .spinner').size() > '0'){
																			$('.index-document-functions.acces_'+num+' .spinner').remove();
																		}
																		$('.index-document-functions.acces_'+num).append('<div class="availability"><span class="label label-success onloan"><a href="#collapseItems'+ biblionumber +'" data-parent="#accordion" data-toggle="collapse">Consultable</span> en bibliothèque </a></div>');
																	}
																	var toAppend = jQuery('<div class="items"></div>').html('<span class="whereis">'+item.homebranch+' </span> | <span>' + item.notforloan + '</span> | <span class="cote">'+item.itemcallnumber+'</span>');
																	jQuery(divItems).makeAppear(toAppend);
																}
															});
															jQuery('.index-document-functions.acces_'+num+'').append(divItems);
														}
													});
												}
												else {
													if($('.index-document-functions.acces_'+num+' .spinner').size() > '0'){
														$('.index-document-functions.acces_'+num+' .spinner').remove();
													}	
												}
											}
										});										
									}
									else {
													if($('.index-document-functions.acces_'+num+' .spinner').size() > '0'){
														$('.index-document-functions.acces_'+num+' .spinner').remove();
													}	
										}	
								});
							}
							else {
								if($('.index-document-functions.acces_'+num+' .spinner').size() > '0'){
									$('.index-document-functions.acces_'+num+' .spinner').remove();
								}	
							}
						}
					});
				}	
			}
		});
	}
}
// ***************************
//  jQuery Object Expendation 
// ***************************

(function($){  
	jQuery.fn.makeAppear = function(elt,_fx,_duration,_callback) {
		elt = (jQuery.type(elt) == "string") ? jQuery(elt) : elt;
		var fx = (_fx == "fade" || _fx == "slide") ? _fx : "fade";
		var duration = _duration || 500;
		var callback = _callback || function(){};
		elt.css("display","none");
		this.append(elt);
		switch(fx) {
			case "fade" :
			elt.fadeIn(duration,callback);
			break;
			case "slide" :
			elt.slideDown(duration,callback);
			break;
			default :
			break;
		}
	};
	jQuery.fn.fadeEmptying = function(_fx,_duration,_callback) {
		var fx = (_fx == "fade" || _fx == "slide") ? _fx : "fade";
		var duration = _duration || 500;
		var callback = _callback || function(){};
		var counter = 0;
		var total = this.children().size();
		var afterFX = function() {
			jQuery(this).remove();
			counter ++;
			if (counter == total) {
				callback();
			}
		};
		this.children().each(function() {
			switch(fx) {
				case "fade" :
				jQuery(this).fadeOut(duration,afterFX);
				break;
				case "slide" :
				jQuery(this).slideUp(duration,afterFX);
				break;
				default :
				break;
			}
		});
	};
	})(jQuery);

