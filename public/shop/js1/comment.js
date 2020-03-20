$(function(){
	$(".ellipsis-s").each(function (i) {
		var divH = $(this).height();
	    var $p = $(".ellipsis-text", $(this)).eq(0);    
	    while ($p.outerHeight() > divH) {    
	        $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));    
	    };    
	});
	util.tab(".tab-bar",".tab-hd-item",".tab-item");
})
