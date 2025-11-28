
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Document</title>
<style type="text/css" media="screen">
.table1
{
	width: 597px;
	height: 822px;
	/*background:#8ba987 url('../../dist/img/plantillas/hojaCorreo.jpg') no-repeat center center;*/
}
.bubbly-button {
  font-family: "Helvetica", "Arial", sans-serif;
  display: inline-block;
  font-size: 1em;
  padding: 1em 2em;
  margin-top: 15px;
  margin-bottom: 15px;
  -webkit-appearance: none;
  appearance: none;
  background-color: #28a745;
  color: #fff;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  position: relative;
  transition: transform ease-in 0.1s, box-shadow ease-in 0.25s;
  box-shadow: 0 2px 25px rgba(40, 167, 69, 0.5);
}
.bubbly-button:focus {
  outline: 0;
}
.bubbly-button:before, .bubbly-button:after {
  position: absolute;
  content: "";
  display: block;
  width: 140%;
  height: 100%;
  left: -20%;
  z-index: -1000;
  transition: all ease-in-out 0.5s;
  background-repeat: no-repeat;
}
.bubbly-button:before {
  display: none;
  top: -75%;
  background-image: radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, transparent 20%, #ff0081 20%, transparent 30%), radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, transparent 10%, #ff0081 15%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%);
  background-size: 10% 10%, 20% 20%, 15% 15%, 20% 20%, 18% 18%, 10% 10%, 15% 15%, 10% 10%, 18% 18%;
}
.bubbly-button:after {
  display: none;
  bottom: -75%;
  background-image: radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, transparent 10%, #ff0081 15%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%), radial-gradient(circle, #ff0081 20%, transparent 20%);
  background-size: 15% 15%, 20% 20%, 18% 18%, 20% 20%, 15% 15%, 10% 10%, 20% 20%;
}
.bubbly-button:active {
  transform: scale(0.9);
  background-color: #7db389;
  box-shadow: 0 2px 25px rgba(255, 0, 130, 0.2);
}
.bubbly-button.animate:before {
  display: block;
  animation: topBubbles ease-in-out 0.75s forwards;
}
.bubbly-button.animate:after {
  display: block;
  animation: bottomBubbles ease-in-out 0.75s forwards;
}

@keyframes topBubbles {
  0% {
    background-position: 5% 90%, 10% 90%, 10% 90%, 15% 90%, 25% 90%, 25% 90%, 40% 90%, 55% 90%, 70% 90%;
  }
  50% {
    background-position: 0% 80%, 0% 20%, 10% 40%, 20% 0%, 30% 30%, 22% 50%, 50% 50%, 65% 20%, 90% 30%;
  }
  100% {
    background-position: 0% 70%, 0% 10%, 10% 30%, 20% -10%, 30% 20%, 22% 40%, 50% 40%, 65% 10%, 90% 20%;
    background-size: 0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%;
  }
}
@keyframes bottomBubbles {
  0% {
    background-position: 10% -10%, 30% 10%, 55% -10%, 70% -10%, 85% -10%, 70% -10%, 70% 0%;
  }
  50% {
    background-position: 0% 80%, 20% 80%, 45% 60%, 60% 100%, 75% 70%, 95% 60%, 105% 0%;
  }
  100% {
    background-position: 0% 90%, 20% 90%, 45% 70%, 60% 110%, 75% 80%, 95% 70%, 110% 10%;
    background-size: 0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%;
  }
}	
/* url('https://www.pavas.com/SGMAP/dist/img/plantillas/pestana1.jpg')*/
</style>
</head>
<body>



<div style="margin:0;padding:0;background-color: #28a745">
	<table width="100%" height="100%" style="min-width:348px" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="32px"></tr>
		<tr align="center">
			<td>
				<table border="0" cellspacing="0" cellpadding="0" style="padding-bottom:20px;max-width:600px;min-width:220px">
					<tbody>
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0">
									<tbody>
										<tr>
											<td></td>
											<td>
												<table width="100%" border="0" cellspacing="0" cellpadding="0" style="direction:ltr;padding-bottom:7px;font-family:Roboto-Regular,Helvetica,Arial,sans-serif;"><tbody><tr><td align="center" style="font-weight: bold; font-size: 16px; color: #FFF; text-transform: uppercase; text-align:center">%titulo%</td>
													<td align="right" style="font-family:Roboto-Light,Helvetica,Arial,sans-serif"></td>
													<td align="right" width="35"></td></tr>
												</tbody>
											</table>
											<div style="background-color:#f5f5f5;direction:ltr;padding:22px 16px;margin-bottom:3px">
												<table class="m_6542478942723271434v2rsp" width="100%" border="0" cellspacing="0" cellpadding="0">
													<tbody>
														<tr>
															<td style="vertical-align:top; width: 40px"><img src="https://www.pavas.com.co/controldata/images/msn.png" style="height: 40px"></td>
															<td width="16px"></td><td style="direction:ltr"><span style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:rgba(0,0,0,0.87);line-height:1.6;color:rgba(0,0,0,0.54)">Saludos,  %usunombre% </span>
															</td>
														</tr>
														<tr>
															<td style="vertical-align:top; width: 40px"></td>
															<td width="16px"></td><td style="direction:ltr"><span style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:rgba(0,0,0,0.87);line-height:1.6;color:rgba(0,0,0,0.54)">%informacion%</span>
															</td>
														</tr>
														
													</tbody>
												</table>
											</div>
											<div style="background-color:#f5f5f5;direction:ltr;padding:22px 16px;margin-bottom:3px">
												<table class="m_6542478942723271434v2rsp" width="100%" border="0" cellspacing="0" cellpadding="0">
													<tbody>
														<tr>
															<td style="text-align:center"><span style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:rgba(0,0,0,0.87);line-height:1.6;color:rgba(0,0,0,0.54);">%boton%</span></td>
														</tr>
													</tbody>
												</table>
											</div>
											<div style="background-color:#f5f5f5;direction:ltr;padding:22px 16px;margin-bottom:3px">
												<table class="m_6542478942723271434v2rsp" width="100%" border="0" cellspacing="0" cellpadding="0">
													<tbody>
														<tr>
															<td><span style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:rgba(0,0,0,0.87);line-height:1.6;color:rgba(0,0,0,0.54);"><span style="font-weight: bold;">%detalleinformacion%</span></span>
															
															</td>
														</tr>				
													</tbody>
												</table>
											</div>
										</td>
										<td></td>
									</tr>
									
									
								
								<tr>
									<td></td>
									<td>
										<div style="text-align:left">
											<div style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;color:rgba(0,0,0,0.54);font-size:12px;line-height:20px;padding-top:10px">
												<div>Este mensaje es generado automáticamente por CMAP, por favor no responda a este correo
												</div>
												<!--<div style="direction:ltr; font-weight: bold;color: blue">© <?php echo date('Y')?> PAVAS S.A.S  
												</div>-->
											</div>											
										</div>
									</td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</td>
</tr>
<tr height="32px"></tr>
</tbody>
</table>

</div>

</body>
</html>
