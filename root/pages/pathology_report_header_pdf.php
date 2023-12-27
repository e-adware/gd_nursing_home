<!--<div class="img_header"><img src="../../images/header.jpg" style="width:100%;"/></div>-->
<div>
	<table class="table table-condensed patient_header">
		<tr>
			<th style="width: 15%;">UHID</th>
			<th>: <?php echo $uhid; ?></th>
			<th style="width: 15%;">Bill No.</th>
			<th>: <?php echo $bill_id; ?></th>
		</tr>
		<tr>
			<th>Name</th>
			<th>: <?php echo $pat_info["name"]; ?></th>
			<th>Age/Sex</th>
			<td>: <?php echo $age; ?> / <?php echo $pat_info["sex"]; ?></td>
		</tr>
		<tr>
			<th>Ref Doctor</th>
			<td colspan="3">: <?php echo $ref_doc["ref_name"]; ?></td>
		</tr>
		<tr>
			<td>Sample(s)</td>
			<td colspan="3">: <?php echo $sample_names; ?></td>
		</tr>
		<tr>
			<td>Collection Time</td>
			<td>: <?php if($collection_date){ echo date("d-m-Y",strtotime($collection_date)); ?> <?php echo date("h:i A",strtotime($collection_time)); } ?></td>
			<td>Reporting Time</td>
			<td>: <?php echo date("d-m-Y",strtotime($report_time["date"])); ?> <?php echo date("h:i A",strtotime($report_time["time"])); ?></td>
		</tr>
		<tr>
			<td colspan="4">
				<div>
					<center>
					<img src="../../barcode-master/barcode.php?f=jpg&s=code-128&d=<?php echo $barcode_data;?>&h=45ms=r&tc=white"/>
					</center>
				</div>
			</td>
		</tr>
	</table>
</div>
