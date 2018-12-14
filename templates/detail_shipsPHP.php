<style>
	x-tooltip {
		padding: 2px 4px !important;
	}
	.ezRatio {
		text-align: center;
		padding-bottom: 0.5em;
	}
	.ezRatio p {
		margin: 0 auto;
		font-size: 4em;
		font-weight: bold;
		text-align: center;
		line-height: 1em;
	}
	.ezPlaceIcon {
		width: 84px;
		height: 84px;
		float: right;
		margin-left: 4px;
	}
	.ezRankIcon {
		width: 96px;
		height: 96px;
		float: left;
		margin-left: 4px;
	}
	.ShipIcon {
		width: 107px;
		height: 63px;
		float: left;
		margin-left: 4px;
	}
</style>
<?php
	$NieMaTiera1 = 0;
	$NieMaTiera2 = 0;
	$NieMaTiera3 = 0;
	$NieMaTiera4 = 0;
	$NieMaTiera5 = 0;
	$NieMaTiera6 = 0;
	$NieMaTiera7 = 0;
	$NieMaTiera8 = 0;
	$NieMaTiera9 = 0;
	$NieMaTiera10 = 0;

?>
<div class="ezContent">
		<h1>Okręty</h1>
			<table style="width: 20%; height: 4em">
				<tr>
				<td width="150" align="left"><b><font size="6">Tier I</font></b></td>
			 <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 1){ ?>
							<td width="100px">
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107"></img> <?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera1++;
						}
						if($dlugosc == $NieMaTiera1){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table>
			<table style="width: 20%; height: 4em">
				<tr> 
				<td width="150" align="left"><b><font size="6">Tier II</font></b></td>
			 <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 2){ ?>
							<td width="100px">
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107" ></img><?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera2++;
						}
						if($dlugosc == $NieMaTiera2){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table>
			<table style="width: 20%; height: 4em">
				<tr> 
				<td width="150" align="left"><b><font size="6">Tier III</font></b></td>
			  <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 3){ ?>
							<td width="100px">
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107" ></img><?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera3++;
						}
						if($dlugosc == $NieMaTiera3){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table >		
			<table style="width: 20%; height: 4em">
				<tr>
				<td width="150" align="left"><b><font size="6">Tier IV</font></b></td>
			  <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 4){ ?>
							<td width="100px">
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107" ></img><?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera4++;
						}
						if($dlugosc == $NieMaTiera4){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table>
			<table style="width: 20%; height: 4em">
				<tr>
				<td width="150" align="left"><b><font size="6">Tier V</font></b></td>
			  <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 5){ ?>
							<td width="100px">
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107" ></img><?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera5++;
						}
						if($dlugosc == $NieMaTiera5){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table>
			<table style="width: 20%; height: 4em">
				<tr>
				<td width="150" align="left"><b><font size="6">Tier VI</font></b></td>
			  <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 6){ ?>
							<td width="100px">
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107" ></img><?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera6++;
						}
						if($dlugosc == $NieMaTiera6){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table>
			<table style="width: 20%; height: 4em">
				<tr>
				<td width="150" align="left"><b><font size="6">Tier VII</font></b></td>
			  <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 7){ ?>
							<td width="100px">
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107" ></img><?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera7++;
						}
						if($dlugosc == $NieMaTiera7){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table>
			<table style="width: 20%; height: 4em">
				<tr>
				<td width="150" align="left"><b><font size="6">Tier VIII</font></b></td>
			  <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 8){ ?>
							<td width="100px">
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107" ></img><?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera8++;
						}
						if($dlugosc == $NieMaTiera8){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table>
			<table style="width: 20%; height: 4em">
				<tr>
				<td width="150" align="left"><b><font size="6">Tier IX</font></b></td>
			  <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 9){ ?>
							<td width="100px">
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107" ></img><?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera9++;
						}
						if($dlugosc == $NieMaTiera9){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table>
			<table style="width: 20%; height: 4em">
				<tr>
				<td width="150" align="left"><b><font size="6">Tier X</font></b></td>
			  <td><?php for($i2 = 0; $i2 < $dlugosc; $i2++){ 
						if($ship_tier[$i2] == 10){ ?>
							<td>
								<a href="detail_ship.php?ShipID=<?php echo $ship_id2[$i2]; ?>&pid=<?php echo $playerid; ?>&plugin=<?php echo $plugin; ?>">
									<?php if(isset($ship_img[$i2])){ ?><img src="<?php echo $ship_img[$i2]; ?>" title="<?php echo $ship_name[$i2]; ?>" height="63" width="107" ></img><?php }else{ echo $ship_name[$i2]; } ?>
								</a>
							</td>
			  <?php 	}else{
							$NieMaTiera10++;
						}
						if($dlugosc == $NieMaTiera10){ ?>
							<td>
							<p align="left"></td>
				<?php				
						}
					} ?></td>
					</tr>
			</table>
	
	<div class="clearfix"></div>
	
	<br/>
	<div class="tleft"><small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Wszystkie statystyki są podane dla bitew PVP</small></div>
	<div class="tright"><small>Profil utworzony: <?php echo $created ?></small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small> Profil zaktualizowany: <?php echo $state; ?></small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
</div>