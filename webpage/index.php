<html>
 <head>
  <title>Tim's Webpage</title>  
 
  <style>
	table, th, td {
	    border: 1px solid black;
	    border-collapse: collapse;
	}
	body {
    font-size: 12pt;
    font-family: Calibri;
    padding : 10px;
}
th {
    border: 1px solid black;
    padding: 5px;
    background-color:grey;
    color: white;
}
td {
    border: 1px solid black;
    padding: 5px;
}
input {
    font-size: 12pt;
    font-family: Calibri;
}
</style>
<script type='text/javascript' src='JS/sorttable.js'></script>
<script type='text/javascript' src='https://code.jquery.com/jquery-1.11.0.min.js'></script>
        <!-- If you want to use jquery 2+: https://code.jquery.com/jquery-2.1.0.min.js -->
        <script type='text/javascript'>
        $(document).ready(function () {

            console.log("HELLO")
            function exportTableToCSV($table, filename) {
            	console.log($table)
            	console.log(filename)


                var $headers = $table.find('tr:has(th)')
                    ,$rows = $table.find('tr:has(td)')

                    // Temporary delimiter characters unlikely to be typed by keyboard
                    // This is to avoid accidentally splitting the actual contents
                    ,tmpColDelim = String.fromCharCode(11) // vertical tab character
                    ,tmpRowDelim = String.fromCharCode(0) // null character

                    // actual delimiter characters for CSV format
                    ,colDelim = '","'
                    ,rowDelim = '"\r\n"';

                    // Grab text from table into CSV formatted string
                    var csv = '"';
                    csv += formatRows($headers.map(grabRow));
                    csv += rowDelim;
                    csv += formatRows($rows.map(grabRow)) + '"';

                    // Data URI
                    var csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);
                    console.log(csvData)

                $(this)
                    .attr({
                    'download': filename
                        ,'href': csvData
                        ,'target' : '_blank' //if you want it to open in a new window
                });

                //------------------------------------------------------------
                // Helper Functions 
                //------------------------------------------------------------
                // Format the output so it has the appropriate delimiters
                function formatRows(rows){
                    return rows.get().join(tmpRowDelim)
                        .split(tmpRowDelim).join(rowDelim)
                        .split(tmpColDelim).join(colDelim);
                }
                // Grab and format a row from the table
                function grabRow(i,row){
                     
                    var $row = $(row);
                    //for some reason $cols = $row.find('td') || $row.find('th') won't work...
                    var $cols = $row.find('td'); 
                    if(!$cols.length) $cols = $row.find('th');  

                    return $cols.map(grabCol)
                                .get().join(tmpColDelim);
                }
                // Grab and format a column from the table 
                function grabCol(j,col){
                    var $col = $(col),
                        $text = $col.text();

                    return $text.replace('"', '""'); // escape double quotes

                }
            }


            // This must be a hyperlink
            $("#export").click(function (event) {
                // var outputFile = 'export'

                var outputFile = window.prompt("What do you want to name your output file (Note: This won't have any effect on Safari)") || 'export';
                outputFile = outputFile.replace('.csv','') + '.csv'
                 
                // CSV
                exportTableToCSV.apply(this, [$('#dvData>table'), outputFile]);
                
                // IF CSV, don't do event.preventDefault() or return false
                // We actually need this to be a typical hyperlink
            });
        });
    </script>
 </head>
 <body>
 <?php
   date_default_timezone_set('Asia/Taipei');

   $dbhost = '127.0.0.1:8889';
   $dbuser = 'root';
   $dbpass = 'root';
   $dbname = "stockdata";
   $today = new DateTime();
   date_time_set($today, 0, 0, 0);
   #echo $today->format("d-m-y h:i:s");


   $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	//echo "Connected successfully";

   
   #echo 'Connected successfully';
   #mysql_close($conn);
	/*
	1st column = SOD SSQ
2nd column = EOD SSQ
3rd column = Update time for EOD
*/
?>


<!--<input type="button" id="export" value=" Export Table data into Excel " />-->
            <div class='button'>
                <a href="#" id ="export" role='button'>Click On This Here Link To Export The Table Data into a CSV File
                </a>
            </div>

<div id="dvData">
<table class="sortable">
	<tr>
		<th>Ticker</th>
	    <th>SOD SS Quota</th>		
	    <th>Quota Left</th>		
		<th>Last Modified </th>		
		<th>% SS Quota Used 1 Day</th>
		<th>% SS Quota Used 1 Week</th>
		<th>% SS Quota Used 1 Month</th>
		<th>% SS Quota Used 3 Months</th>
		<th>% SS Quota Used 6 Months</th>
		<th>% SS Quota Used 1 Year</th>
	</tr>
	<?php 
		#echo "hurray";
		#$today = mysql_real_escape_string($today);
		#$result = mysql_query("SELECT * FROM stockdata.sbl_info WHERE current_date >='{$today}'") or die(mysql_error());
		#$result = mysql_query("SELECT * FROM stockdata.SBL_info") or die(mysql_error());
	$sql = "SELECT distinct(ticker) as tick, sbl_qty FROM stockdata.SBL_info WHERE current_date >= curdate() group by tick"; #.$today;
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	$ticker = $row["tick"];
	    	$sbl_sod = $row["sbl_qty"];

	    	$sql_temp = "SELECT avail, extract(hour from last_modify) as last_hour,extract(minute from last_modify) as last_min,extract(second from last_modify) as last_sec  FROM stockdata.Avail_info WHERE created_at >= curdate() and ticker = '".$ticker ."'"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$avail_qty = $row_temp["avail"];
				$last_hour = $row_temp["last_hour"];
				$last_min = $row_temp["last_min"];
				$last_sec = $row_temp["last_sec"];
				if ($last_hour > 0) {
					$last_modify = "$last_hour:$last_min:$last_sec";
				} 
				else {
					$last_modify = "";
				}
			}
			else {
				$avail_qty = 0;		
				$last_modify = "";	
	
			}


// Weekly comparison

			$sql_temp = "SELECT sum(sbl_qty) FROM stockdata.SBL_info WHERE created_at >= curdate()-7 and ticker = '".$ticker ."' group by cast(created_at as date)" ; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$week_sbl = $row_temp["sum(sbl_qty)"];
			}
			else {
				$week_sbl = 0;			
			}

			$sql_temp = "SELECT sum(avail) FROM stockdata.Avail_info WHERE created_at >= curdate()-7 and ticker = '".$ticker ."' group by cast(created_at as date)"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$week_qty = $row_temp["sum(avail)"];
			}
			else {
				$week_qty = 0;			
			}

// Monthly comparison

			$sql_temp = "SELECT sum(sbl_qty) FROM stockdata.SBL_info WHERE created_at >= curdate()-30 and ticker = '".$ticker ."' group by cast(created_at as date)"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$month_sbl = $row_temp["sum(sbl_qty)"];
			}
			else {
				$month_sbl = 0;			
			}

			$sql_temp = "SELECT sum(avail) FROM stockdata.Avail_info WHERE created_at >= curdate()-30 and ticker = '".$ticker ."' group by cast(created_at as date)"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$month_qty = $row_temp["sum(avail)"];
			}
			else {
				$month_qty = 0;			
			}

// Quarterly comparison

			$sql_temp = "SELECT sum(sbl_qty) FROM stockdata.SBL_info WHERE created_at >= curdate()-91 and ticker = '".$ticker ."' group by cast(created_at as date)"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$quart_sbl = $row_temp["sum(sbl_qty)"];
			}
			else {
				$quart_sbl = 0;			
			}

			$sql_temp = "SELECT sum(avail) FROM stockdata.Avail_info WHERE created_at >= curdate()-91 and ticker = '".$ticker ."' group by cast(created_at as date)"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$quart_qty = $row_temp["sum(avail)"];
			}
			else {
				$quart_qty = 0;			
			}

// Half yearly comparison

			$sql_temp = "SELECT sum(sbl_qty) FROM stockdata.SBL_info WHERE created_at >= curdate()-182 and ticker = '".$ticker ."' group by cast(created_at as date)"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$half_sbl = $row_temp["sum(sbl_qty)"];
			}
			else {
				$half_sbl = 0;			
			}

			$sql_temp = "SELECT sum(avail) FROM stockdata.Avail_info WHERE created_at >= curdate()-182 and ticker = '".$ticker ."' group by cast(created_at as date)"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$half_qty = $row_temp["sum(avail)"];
			}
			else {
				$half_qty = 0;			
			}

// yearly comparison

			$sql_temp = "SELECT sum(sbl_qty) FROM stockdata.SBL_info WHERE created_at >= curdate()-365 and ticker = '".$ticker ."' group by cast(created_at as date)"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$year_sbl = $row_temp["sum(sbl_qty)"];
			}
			else {
				$year_sbl = 0;			
			}

			$sql_temp = "SELECT sum(avail) FROM stockdata.Avail_info WHERE created_at >= curdate()-365 and ticker = '".$ticker ."' group by cast(created_at as date)"; #.$today;
			$result_temp = $conn->query($sql_temp);
			if ($result_temp->num_rows > 0) {
				$row_temp = $result_temp->fetch_assoc();
				$year_qty = $row_temp["sum(avail)"];
			}
			else {
				$year_qty = 0;			
			}

			$dayquota = ($sbl_sod-$avail_qty)*100/$sbl_sod;
			$weekquota = ($week_sbl-$week_qty)*100/$week_sbl;
			$monthquota = ($month_sbl-$month_qty)*100/$month_sbl;
			$quartquota = ($quart_sbl-$quart_qty)*100/$quart_sbl;
			$halfquota = ($half_sbl-$half_qty)*100/$half_sbl;
			$yearquota = ($year_sbl-$year_qty)*100/$year_sbl;

	    	echo '<tr>
				<td> '. $ticker. '</td>
				<td>'. number_format($sbl_sod). '</td>	
				<td>'. number_format($avail_qty). '</td>	
				<td>'. $last_modify. '</td>	
				<td>'. number_format($dayquota, 2, '.',','). '%</td>	
				<td>'. number_format($weekquota, 2, '.',','). '%</td>	
				<td>'. number_format($monthquota, 2, '.',','). '%</td>	
				<td>'. number_format($quartquota, 2, '.',','). '%</td>	
				<td>'. number_format($halfquota, 2, '.',','). '%</td>	
				<td>'. number_format($yearquota, 2, '.',','). '%</td>	

			</tr>'; 
			}
	    }
$conn->close();			    
	?>
</table>
</div>

 </body>
</html>