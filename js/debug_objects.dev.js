function toggle(obj) {
	var el = document.getElementById(obj);
	if ( el.style.display != 'block' ) {
		el.style.display = 'block';
	}	else {
		el.style.display = 'none';
	}
}

jQuery(function($) {
	$(function() {
		$("#debugobjectstabs").tabs();
	});
});