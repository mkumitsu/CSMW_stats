<head>
	<script src="https://www.google.com/jsapi" type="text/javascript"></script>
	<script type="text/javascript">google.load("jquery", "1");</script>
	
	<script type="text/javascript">
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "%PATH%index.php",
				success: function(data){
					$("#ezInclude").html(data);
				}
			});
		});
	</script>
</head>

<div id="ezInclude"></div>
