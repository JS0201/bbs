var util = function(){
	return {
		newsSlip : function (ele,interval,duration){
			var	ele = $(ele),
				clone = ele.find('.news-slip-item:first').clone(),
				itemWrap = ele.find('.news-slip-cont');
				itemWrap.append(clone);
			var items = ele.find('.news-slip-item'),
				len = items.length,
				hig = items.outerHeight(),
				index = 0;

				setInterval(function(){
					if(index == len-1){
						index = 1;
						itemWrap.css({'top':'0'})
					}else{index ++}; 

					itemWrap.stop().animate({top:-parseInt(hig)*index+"px"},duration);
				},interval);
		},
		tab : function (ele,eleh,eleb){
			var ele = $(ele),
				eleh = $(eleh),
				eleb = $(eleb);
			eleh.on("tap",function(){
				var index = $(this).index();
				$(this).addClass('cur').siblings().removeClass('cur');
				console.log(index)
				$(this).parents(ele).find(eleb).eq(index).show().siblings().hide();
			})
		}
	}
}();
