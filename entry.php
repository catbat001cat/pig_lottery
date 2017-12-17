
<?php
$channel = $_REQUEST['channel'];
?>

<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=y6UTNTej5ZmDH9W4iHmXbPSp"></script>
<script type="text/javascript" src="http://developer.baidu.com/map/jsdemo/demo/convertor.js"></script>  

<script>
function showPosition(position)
{
	var lat = position.latitude;
	var lng = position.longitude;
	var city = position.address.city;
	alert('当前所在:' + city);
	<?php 
	$url = "index.php?g=portal&m=index&a=any_login&channel=".$channel;
	echo "var url='" . $url . "&lat='+lat+'&lng='+lng+'&city='+encodeURI(city);";
	echo 'window.location.href=url;';
	?>
}

var geolocation = new BMap.Geolocation();  
geolocation.getCurrentPosition(showPosition);
</script>