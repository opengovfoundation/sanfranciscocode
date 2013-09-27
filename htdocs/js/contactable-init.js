function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

$(document).ready(function(){
	$('#contactable').contactable({
		subject: 'Feedback Message',
		url: '/mail.php'
	});

	$('#stay-updated').click(function(){
		var email = $('#signup-email').val();

		if(!validateEmail(email)){
			$('#submit_response').css('color', 'red').html('Please enter a valid email address');
			return;
		}

		$.post('signup.php', {'email': email}, function(data){
			data = JSON.parse(data);

			if(data.success == true){
				$('#submit_response').css('color', 'green').html(data.msg);
			}else{
				$('#submit_response').css('color', 'red').html(data.msg);
			}
		});
	});
});