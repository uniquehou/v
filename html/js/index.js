var a = document.querySelectorAll("a.mui-tab-item");
for(i=0; i<a.length; i++) {
	a[i].classList.remove("mui-active");
}

mui('.mui-bar-tab').on('tap', 'a', function(e) {
	window.location.href =  this.getAttribute('href');
});
			
var first = null;
mui.back = function() {
	if (!first) {
		first = new Date().getTime();
		plus.nativeUI.toast("再按一次退出应用");
		setTimeout(function() {
			first = null;
		}, 1000);
	} else {
		if (new Date().getTime() - first < 1000) {
			plus.runtime.quit();
		}
	}
}