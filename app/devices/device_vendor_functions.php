<?php

//includes
	require_once "root.php";
	require_once "resources/require.php";

//check permissions
	require_once "resources/check_auth.php";
	if (permission_exists('device_vendor_function_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get variables used to control the order
	$order_by = check_str($_GET["order_by"]);
	$order = check_str($_GET["order"]);

//add the search term
	$search = check_str($_GET["search"]);
	if (strlen($search) > 0) {
		$sql_search = "and (";
		$sql_search .= "label like '%".$search."%'";
		$sql_search .= "or name like '%".$search."%'";
		$sql_search .= "or value like '%".$search."%'";
		$sql_search .= "or enabled like '%".$search."%'";
		$sql_search .= "or description like '%".$search."%'";
		$sql_search .= ")";
	}
//additional includes
	require_once "resources/header.php";
	require_once "resources/paging.php";

//prepare to page the results
	$sql = "select count(*) as num_rows from v_device_vendor_functions ";
	$sql .= "where device_vendor_uuid = '$device_vendor_uuid' ";
	$sql .= $sql_search;
	if (strlen($order_by)> 0) { $sql .= "order by $order_by $order "; }
	$prep_statement = $db->prepare($sql);
	if ($prep_statement) {
		$prep_statement->execute();
		$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
		if ($row['num_rows'] > 0) {
				$num_rows = $row['num_rows'];
		}
		else {
				$num_rows = '0';
		}
	}

//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = "";
	$page = $_GET['page'];
	if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
	list($paging_controls, $rows_per_page, $var3) = paging($num_rows, $param, $rows_per_page);
	$offset = $rows_per_page * $page;

//get the list
	$sql = "select * from v_device_vendor_functions ";
	$sql .= "where device_vendor_uuid = '$device_vendor_uuid' ";
	$sql .= $sql_search;
	if (strlen($order_by)> 0) { $sql .= "order by $order_by $order "; }
	$sql .= "limit $rows_per_page offset $offset ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	unset ($prep_statement, $sql);

//alternate the row style
	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

//show the content
	echo "<table width='100%' border='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='50%' align='left' nowrap='nowrap'><b>".$text['title-device_vendor_functions']."</b></td>\n";
	echo "		<form method='get' action=''>\n";
	echo "			<td width='50%' style='vertical-align: top; text-align: right; white-space: nowrap;'>\n";
	echo "				<input type='text' class='txt' style='width: 150px' name='search' id='search' value='".$search."'>\n";
	echo "				<input type='submit' class='btn' name='submit' value='".$text['button-search']."'>\n";
	echo "			</td>\n";
	echo "		</form>\n";
	echo "	</tr>\n";
	echo "</table>\n";

	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo th_order_by('label', $text['label-label'], $order_by, $order);
	echo th_order_by('name', $text['label-name'], $order_by, $order);
	echo th_order_by('value', $text['label-value'], $order_by, $order);
	echo th_order_by('enabled', $text['label-enabled'], $order_by, $order);
	echo th_order_by('description', $text['label-description'], $order_by, $order);
	echo "<td class='list_control_icons'>";
	if (permission_exists('device_vendor_function_add')) {
		echo "<a href='device_vendor_function_edit.php?device_vendor_uuid=".$_GET['id']."' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	else {
		echo "&nbsp;\n";
	}
	echo "</td>\n";
	echo "<tr>\n";

	if (is_array($result)) {
		foreach($result as $row) {
			if (permission_exists('device_vendor_function_edit')) {
				$tr_link = "href='device_vendor_function_edit.php?device_vendor_uuid=".$row['device_vendor_uuid']."&id=".$row['device_vendor_function_uuid']."'";
			}
			echo "<tr ".$tr_link.">\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".$row['label']."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".$row['name']."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".$row['value']."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".$row['enabled']."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".$row['description']."&nbsp;</td>\n";
			echo "	<td class='list_control_icons'>";
			if (permission_exists('device_vendor_function_edit')) {
				echo "<a href='device_vendor_function_edit.php?device_vendor_uuid=".$row['device_vendor_uuid']."&id=".$row['device_vendor_function_uuid']."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('device_vendor_function_delete')) {
				echo "<a href='device_vendor_function_delete.php?device_vendor_uuid=".$row['device_vendor_uuid']."&id=".$row['device_vendor_function_uuid']."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
			}
			echo "	</td>\n";
			echo "</tr>\n";
			if ($c==0) { $c=1; } else { $c=0; }
		} //end foreach
		unset($sql, $result, $row_count);
	} //end if results

	echo "<tr>\n";
	echo "<td colspan='6' align='left'>\n";
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='33.3%' nowrap='nowrap'>&nbsp;</td>\n";
	echo "		<td width='33.3%' align='center' nowrap='nowrap'>$paging_controls</td>\n";
	echo "		<td class='list_control_icons'>";
	if (permission_exists('device_vendor_function_add')) {
		echo 		"<a href='device_vendor_function_edit.php?device_vendor_uuid=".$_GET['id']."' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	else {
		echo 		"&nbsp;";
	}
	echo "		</td>\n";
	echo "	</tr>\n";
 	echo "	</table>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>";
	echo "<br /><br />";

//include the footer
	require_once "resources/footer.php";

?>