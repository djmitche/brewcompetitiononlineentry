<?php 

session_start(); 
require('../paths.php'); 
require(CONFIG.'bootstrap.php');
require(DB.'winners.db.php');

$query_prefs = sprintf("SELECT prefsWinnerMethod FROM %s WHERE id=1", $prefix."preferences");
$prefs = mysql_query($query_prefs, $brewing) or die(mysql_error());
$row_prefs = mysql_fetch_assoc($prefs);

if ($view == "pdf") {
	require(CLASSES.'fpdf/html_table.php');
	$pdf=new PDF();
	$pdf->AddPage();
	
}


if ($view == "html") {
	$header .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	$header .= '<html xmlns="http://www.w3.org/1999/xhtml">';
	$header .= '<head>';
	$header .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	$header .= '<title>Results - '.$_SESSION['contestName'].'</title>';
	$header .= '</head>';
	$header .= '<body>';
}

if ($go == "judging_scores_bos") { 
if ($view == "pdf") {
	$pdf->SetFont('Arial','B',16);
	$pdf->Write(5,'Best of Show Results - '.$_SESSION['contestName']);
	$pdf->SetFont('Arial','',7);	
}
$filename = str_replace(" ","_",$_SESSION['contestName']).'_BOS_Results.'.$view;

do { $a[] = $row_style_types['id']; } while ($row_style_types = mysql_fetch_assoc($style_types));
$html == '';
if ($view == "html") $html .= '<h1>BOS - '.$_SESSION['contestName'].'</h1>';
sort($a);
foreach (array_unique($a) as $type) {
	$query_style_type = "SELECT * FROM $style_types_db_table WHERE id='$type'";
	$style_type = mysql_query($query_style_type, $brewing) or die(mysql_error());
	$row_style_type = mysql_fetch_assoc($style_type);

	if ($row_style_type['styleTypeBOS'] == "Y") { 
	$query_bos = sprintf("SELECT a.scorePlace, b.brewName, b.brewCategory, b.brewCategorySort, b.brewSubCategory, b.brewStyle, b.brewCoBrewer, c.brewerLastName, c.brewerFirstName, c.brewerClubs FROM %s a, %s b, %s c WHERE a.eid = b.id AND a.scorePlace IS NOT NULL AND c.uid = b.brewBrewerID AND scoreType='%s' ORDER BY a.scorePlace", $prefix."judging_scores_bos", $prefix."brewing", $prefix."brewer", $type);
	$bos = mysql_query($query_bos, $brewing) or die(mysql_error());
	$row_bos = mysql_fetch_assoc($bos);
	$totalRows_bos = mysql_num_rows($bos);
	
	//echo $query_bos;
	
if ($totalRows_bos > 0) { 
	$html .= '<br><br><strong>'.$row_style_type['styleTypeName'].'</strong><br>';
	$html .= '<table border="1">';
	$html .= '<tr>';
	$html .= '<td width="35"  align="center" bgcolor="#cccccc" nowrap="nowrap"><strong>Place</strong></td>';
	$html .= '<td width="150" align="center" bgcolor="#cccccc"><strong>Brewer(s)</strong></td>';
	$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Entry Name</strong></td>';
	$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Style</strong></td>';
	$html .= '<td width="175" align="center" bgcolor="#cccccc"><strong>Club</strong></td>';
	$html .= '</tr>';
	do {
		
		$style = $row_bos['brewCategory'].$row_bos['brewSubCategory'];
		
			$html .= '<tr>';
			$html .= '<td width="35" nowrap="nowrap">'.display_place($row_bos['scorePlace'],1).'</td>';
			$html .= '<td width="150">'.$row_bos['brewerFirstName'].' '.$row_bos['brewerLastName'];
			if ($row_entries['brewCoBrewer'] != "") $html .=', '.$row_bos['brewCoBrewer'];
			$html .= '</td>';
			$html .= '<td width="200">'.strtr($row_bos['brewName'],$html_remove).'</td>';
			$html .= '<td width="200">'.$style.': '.$row_bos['brewStyle'].'</td>';
			$html .= '<td width="175">';
			if ($row_bos['brewerClubs'] != "") $html .=strtr($row_bos['brewerClubs'],$html_remove);
			else $html .= "&nbsp;";
			$html .= '</td>';
			$html .= '</tr>';
	} while ($row_bos = mysql_fetch_assoc($bos)); 
	mysql_free_result($bos);
	$html .= '</table>';
	  } 
    }
  }

  if ($totalRows_sbi > 0) {	
  
  	do {
		$query_sbd = sprintf("SELECT * FROM $special_best_data_db_table WHERE sid='%s' ORDER BY sbd_place ASC",$row_sbi['id']);
		$sbd = mysql_query($query_sbd, $brewing) or die(mysql_error());
		$row_sbd = mysql_fetch_assoc($sbd);
		$totalRows_sbd = mysql_num_rows($sbd);
		
			$html .= '<br><br><strong>'.strtr($row_sbi['sbi_name'],$html_remove).'</strong>';
			$html .= '<br>'.strtr($row_sbi['sbi_description'],$html_remove).'<br>';
			$html .= '<table border="1">';
			$html .= '<tr>';
			if ($row_sbi['sbi_display_places'] == "1") $html .= '<td width="35" align="center"  bgcolor="#cccccc" nowrap="nowrap"><strong>Place</strong></td>';
			$html .= '<td width="150" align="center" bgcolor="#cccccc"><strong>Brewer(s)</strong></td>';
			$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Entry Name</strong></td>';
			$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Style</strong></td>';
			$html .= '<td width="175" align="center" bgcolor="#cccccc"><strong>Club</strong></td>';
			$html .= '</tr>';
			
			do { 
				$brewer_info = explode("^",brewer_info($row_sbd['bid']));
				$entry_info = explode("^",entry_info($row_sbd['eid']));
				$style = $entry_info['5'].$entry_info['2'];
				$html .= '<tr>';
				if ($row_sbi['sbi_display_places'] == "1") { $html .= '<td width="35" nowrap="nowrap">'.display_place($row_sbd['sbd_place'],4).'</td>'; }
				$html .= '<td width="150">'.$brewer_info['0']." ".$brewer_info['1']; 
					if ($row_entries['brewCoBrewer'] != "") $html .= "<br />Co-Brewer: ".$entry_info['4']; 
				$html .= '</td>';
				$html .= '<td width="200">'.strtr($entry_info['0'],$html_remove).'</td>';
				$html .= '<td width="200">'.$style.": ".$entry_info['3'].'</td>';
				$html .= '<td width="175">';
				if ($brewer_info['7'] != "") $html .=strtr($brewer_info['7'],$html_remove);
				else $html .= "&nbsp;";
				$html .= '</td>';   
				$html .= '</tr>';
				if ($row_sbd['sbd_comments'] != "") {
					if ($row_sbi['sbi_display_places'] == "1") $html .= '<td width="760" colspan="5"><em>'.$row_sbd['sbd_comments'].'</em></td>';
					else $html .= '<td width="725" colspan="4"><em>'.$row_sbd['sbd_comments'].'</em></td>';
					$html .= '</tr>';
				}
			} while ($row_sbd = mysql_fetch_assoc($sbd));
		
	} while ($row_sbi = mysql_fetch_assoc($sbi));	
  }
 	if ($view == "pdf") { 
	$html = iconv('UTF-8', 'windows-1252', $html);	
	$pdf->WriteHTML($html); 
	}
	//echo $html;
} // end if ($go == "judging_scores_bos")


if ($go == "judging_scores") {
if ($view == "pdf") {
	$pdf->SetFont('Arial','B',16);
	$pdf->Write(5,'Results - '.$_SESSION['contestName']);
	$pdf->SetFont('Arial','',7);	
}
$filename = str_replace(" ","_",$_SESSION['contestName']).'_Results.'.$view;
$html = '';
if ($view == "html") $html .= '<h1>Results - '.$_SESSION['contestName'].'</h1>';

	if ($row_prefs['prefsWinnerMethod'] == 1) {
		
		$query_styles = "SELECT brewStyleGroup FROM $styles_db_table WHERE brewStyleActive='Y' ORDER BY brewStyleGroup ASC";
		$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
		$row_styles = mysql_fetch_assoc($styles);
		$totalRows_styles = mysql_num_rows($styles);
		do { $style[] = $row_styles['brewStyleGroup']; } while ($row_styles = mysql_fetch_assoc($styles));
	
		foreach (array_unique($style) as $style) {
			$query_entry_count = sprintf("SELECT COUNT(*) as 'count' FROM %s WHERE brewCategorySort='%s' AND brewReceived='1'", $brewing_db_table,  $style);
			$entry_count = mysql_query($query_entry_count, $brewing) or die(mysql_error());
			$row_entry_count = mysql_fetch_assoc($entry_count);
			
			$query_score_count = sprintf("SELECT  COUNT(*) as 'count' FROM %s a, %s b, %s c WHERE b.brewCategorySort='%s' AND a.eid = b.id AND a.scorePlace IS NOT NULL AND c.uid = b.brewBrewerID", $judging_scores_db_table, $brewing_db_table, $brewer_db_table, $style);
			$score_count = mysql_query($query_score_count, $brewing) or die(mysql_error());
			$row_score_count = mysql_fetch_assoc($score_count);
			
			if (($row_entry_count['count'] > 0) && ($row_score_count['count'] > 0)) {
			$html .= '<br><br><strong>Category '.ltrim($style,"0").': '.style_convert($style,"1").' ('.$row_entry_count['count'].' entries)</strong><br>';
            $html .= '<table border="1">';
			$html .= '<tr>';
			$html .= '<td width="35" align="center"  bgcolor="#cccccc" nowrap="nowrap"><strong>Pl.</strong></td>';
			$html .= '<td width="150" align="center" bgcolor="#cccccc"><strong>Brewer(s)</strong></td>';
			$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Entry Name</strong></td>';
			$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Style</strong></td>';
			$html .= '<td width="175" align="center" bgcolor="#cccccc"><strong>Club</strong></td>';
			$html .= '</tr>';
		 
			$query_scores = sprintf("SELECT a.scorePlace, a.scoreEntry, b.brewName, b.brewCategory, b.brewCategorySort, b.brewSubCategory, b.brewStyle, b.brewCoBrewer, c.brewerLastName, c.brewerFirstName, c.brewerClubs FROM %s a, %s b, %s c WHERE b.brewCategorySort='%s' AND a.eid = b.id AND a.scorePlace IS NOT NULL AND c.uid = b.brewBrewerID  AND (a.scorePlace IS NOT NULL OR a.scorePlace='') ORDER BY a.scorePlace", $judging_scores_db_table, $brewing_db_table, $brewer_db_table, $style);
			$scores = mysql_query($query_scores, $brewing) or die(mysql_error());
			$row_scores = mysql_fetch_assoc($scores);
			$totalRows_scores = mysql_num_rows($scores);
					
			do { 
				$style = $row_scores['brewCategory'].$row_scores['brewSubCategory'];
				$html .= '<tr>';
				$html .= '<td width="35">'.display_place($row_scores['scorePlace'],1).'</td>';
				$html .= '<td width="150">'.$row_scores['brewerFirstName'].' '.$row_scores['brewerLastName'].'</td>';
				$html .= '<td width="200">';
				if ($row_scores['brewName'] != '') $html .= strtr($row_scores['brewName'],$html_remove); else $html .= '&nbsp;';
				$html .= '</td>';
				$html .= '<td width="200">';
				if ($row_scores['brewStyle'] != '') $html .= $row_scores['brewStyle']; else $html .= "&nbsp;";
				$html .= '</td>';
				$html .= '<td width="175">';
				if ($row_scores['brewerClubs'] != "") $html .=strtr($row_scores['brewerClubs'],$html_remove);
				else $html .= "&nbsp;";
				$html .= '</td>';
				$html .= '</tr>';
			} while ($row_scores = mysql_fetch_assoc($scores));
			$html .= '</table>';
			} 
		} 
	} // end if ($row_prefs['prefsWinnerMethod'] == "1") 
	
	if ($row_prefs['prefsWinnerMethod'] == 2) {
		
		$query_styles = "SELECT brewStyleGroup,brewStyleNum,brewStyle FROM $styles_db_table WHERE brewStyleActive='Y' ORDER BY brewStyleGroup,brewStyleNum ASC";
		$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
		$row_styles = mysql_fetch_assoc($styles);
		$totalRows_styles = mysql_num_rows($styles);
		do { $style[] = $row_styles['brewStyleGroup']."-".$row_styles['brewStyleNum']."-".$row_styles['brewStyle']; } while ($row_styles = mysql_fetch_assoc($styles));

		foreach (array_unique($style) as $style) {
			$style = explode("-",$style);
			$query_entry_count = sprintf("SELECT COUNT(*) as 'count' FROM %s WHERE brewCategorySort='%s' AND brewSubCategory='%s' AND brewReceived='1'", $brewing_db_table,  $style[0], $style[1]);
			$entry_count = mysql_query($query_entry_count, $brewing) or die(mysql_error());
			$row_entry_count = mysql_fetch_assoc($entry_count);
			
			$query_score_count = sprintf("SELECT  COUNT(*) as 'count' FROM %s a, %s b, %s c WHERE b.brewCategorySort='%s' AND b.brewSubCategory='%s' AND a.eid = b.id AND a.scorePlace IS NOT NULL AND c.uid = b.brewBrewerID", $judging_scores_db_table, $brewing_db_table, $brewer_db_table, $style[0], $style[1]);
			$score_count = mysql_query($query_score_count, $brewing) or die(mysql_error());
			$row_score_count = mysql_fetch_assoc($score_count);
			
			if (($row_entry_count['count'] > 0) && ($row_score_count['count'] > 0)) {
			$html .= '<br><br><strong>Category '.ltrim($style[0],"0").$style[1].': '.$style[2].' ('.$row_entry_count['count'].' entries)</strong><br>';
            $html .= '<table border="1">';
			$html .= '<tr>';
			$html .= '<td width="35" align="center"  bgcolor="#cccccc" nowrap="nowrap"><strong>Pl.</strong></td>';
			$html .= '<td width="150" align="center" bgcolor="#cccccc"><strong>Brewer(s)</strong></td>';
			$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Entry Name</strong></td>';
			$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Style</strong></td>';
			$html .= '<td width="175" align="center" bgcolor="#cccccc"><strong>Club</strong></td>';
			$html .= '</tr>';
		 
			
			$query_scores = sprintf("SELECT a.scorePlace, a.scoreEntry, b.brewName, b.brewCategory, b.brewCategorySort, b.brewSubCategory, b.brewStyle, b.brewCoBrewer, c.brewerLastName, c.brewerFirstName, c.brewerClubs FROM %s a, %s b, %s c WHERE b.brewCategorySort='%s' AND b.brewSubCategory='%s' AND a.eid = b.id  AND c.uid = b.brewBrewerID  AND (a.scorePlace IS NOT NULL OR a.scorePlace='') ORDER BY a.scorePlace", $judging_scores_db_table, $brewing_db_table, $brewer_db_table, $style[0],$style[1]);
			$scores = mysql_query($query_scores, $brewing) or die(mysql_error());
			$row_scores = mysql_fetch_assoc($scores);
			$totalRows_scores = mysql_num_rows($scores);
					
			do { 
				$style = $row_scores['brewCategory'].$row_scores['brewSubCategory'];
				$html .= '<tr>';
				$html .= '<td width="35">'.display_place($row_scores['scorePlace'],1).'</td>';
				$html .= '<td width="150">'.$row_scores['brewerFirstName'].' '.$row_scores['brewerLastName'].'</td>';
				$html .= '<td width="200">';
				if ($row_scores['brewName'] != '') $html .= strtr($row_scores['brewName'],$html_remove); else $html .= '&nbsp;';
				$html .= '</td>';
				$html .= '<td width="200">';
				if ($row_scores['brewStyle'] != '') $html .= $row_scores['brewStyle']; else $html .= "&nbsp;";
				$html .= '</td>';
				$html .= '<td width="175">';
				if ($row_scores['brewerClubs'] != "") $html .=strtr($row_scores['brewerClubs'],$html_remove);
				else $html .= "&nbsp;";
				$html .= '</td>';
				$html .= '</tr>';
			} while ($row_scores = mysql_fetch_assoc($scores));
			$html .= '</table>';
			} 
		}
		
	}

	if ($row_prefs['prefsWinnerMethod'] == 0) {
		do { 
			$entry_count = get_table_info(1,"count_total",$row_tables['id'],$dbTable,"default");
			if ($entry_count > 0) { 
			$html .= '<br><br><strong>Table '.$row_tables['tableNumber'].': '.$row_tables['tableName'].' ('.$entry_count.' entries)</strong><br>';
			$html .= '<table border="1">';
			$html .= '<tr>';
			$html .= '<td width="35" align="center"  bgcolor="#cccccc" nowrap="nowrap"><strong>Pl.</strong></td>';
			$html .= '<td width="150" align="center" bgcolor="#cccccc"><strong>Brewer(s)</strong></td>';
			$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Entry Name</strong></td>';
			$html .= '<td width="200" align="center" bgcolor="#cccccc"><strong>Style</strong></td>';
			$html .= '<td width="175" align="center" bgcolor="#cccccc"><strong>Club</strong></td>';
			$html .= '</tr>';
			
				$query_scores = sprintf("SELECT a.scorePlace, a.scoreEntry, b.brewName, b.brewCategory, b.brewCategorySort, b.brewSubCategory, b.brewStyle, b.brewCoBrewer, c.brewerLastName, c.brewerFirstName, c.brewerClubs FROM %s a, %s b, %s c WHERE scoreTable='%s' AND a.eid = b.id AND c.uid = b.brewBrewerID AND a.scorePlace IS NOT NULL ORDER BY a.scorePlace", $judging_scores_db_table, $brewing_db_table, $brewer_db_table, $row_tables['id']);
				$scores = mysql_query($query_scores, $brewing) or die(mysql_error());
				$row_scores = mysql_fetch_assoc($scores);
				$totalRows_scores = mysql_num_rows($scores);
						
				do { 
				$style = $row_scores['brewCategory'].$row_scores['brewSubCategory'];
				$html .= '<tr>';
				$html .= '<td width="35">'.display_place($row_scores['scorePlace'],1).'</td>';
				$html .= '<td width="150">'.$row_scores['brewerFirstName'].' '.$row_scores['brewerLastName'].'</td>';
				$html .= '<td width="200">';
				if ($row_scores['brewName'] != '') $html .= strtr($row_scores['brewName'],$html_remove); else $html .= '&nbsp;';
				$html .= '</td>';
				$html .= '<td width="200">';
				if ($row_scores['brewStyle'] != '') $html .= $row_scores['brewStyle']; else $html .= "&nbsp;";
				$html .= '</td>';
				$html .= '<td width="175">';
				if ($row_scores['brewerClubs'] != "") $html .=strtr($row_scores['brewerClubs'],$html_remove);
				else $html .= "&nbsp;";
				$html .= '</td>';
				$html .= '</tr>';
				
				//	mysql_free_result($entries);
				} while ($row_scores = mysql_fetch_assoc($scores));
				$html .= '</table>';
			} 
		} while ($row_tables = mysql_fetch_assoc($tables));
	} // end 

if ($view == "pdf") { 
$html = iconv('UTF-8', 'windows-1252', $html);				
$pdf->WriteHTML($html); 
}	
} // end if ($go == "judging_scores")


if ($view == "pdf") { 
	$pdf->Output($filename,'D');
	//echo $html;
	}

if ($view == "html") { 
	$footer = '</body>';
	$footer .= '</html>';
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=".$filename);
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $header.$html.$footer;
	exit();
	}

?>
