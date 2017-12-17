<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" id="viewport" name="viewport">
	<title>客服中心</title>
	<style>
		body{
			margin: 0;
			padding: 0;
			text-align: center;
			background: #f0f0f0;
		}
		.top{
			background: #f25f55;
			text-align: center;
			height: 40px;
			line-height: 30px;
			color: #fff;
			margin-bottom: 0px;
		}
		.tiao{
			background: #fff;
			width: 80%;
			padding: 0 0 1% 0;
			margin: 0 auto;
			border-radius: 10px;
		}
		.tiao p{
			margin: 0;
			color: #f25f55;
			margin-top: 0px;
		}
		.tiao h4{
			background: 
			margin: 0;
			color: #fff;
			background: #3cb034;
			line-height: 30px;
			height: 30px;
		}
	</style>
</head>
<body>
	<div class="top">
		长按以下二维码联系客服
	</div>
	<div class="tiao">
		<h4>QQ客服</h4>
		<img src="<?php echo ($servicer_qr); ?>" alt="" width="50%">
		<p>QQ:XXXXXX</p>
	</div>
	<!--<div class="tiao" style="margin-bottom:80px;">
		<h4>公众号</h4>
		<img src="/images/gzhkf.jpg" alt="" width="50%">
		<p>长按二维码关注公众号</p>
	</div>-->
        <div style="border-top: #F0F0F0 1px solid; height: 45px; width: 100%; line-height: 44px;
        background: white; position: fixed; bottom: 0px; text-align: center; font-size: 16px;"
        onclick="location.href='/'">
        返回首页
    </div>
</body>
</html>