<?php
/**
 * This file is part of The Draconic Ephemeris Generator.
 *
 *  The Draconic Ephemeris Generator is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The Draconic Ephemeris Generator is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with The Draconic Ephemeris Generator.  If not, see <http://www.gnu.org/licenses/>.
 */
	date_default_timezone_set('UTC');
	$days = 36890; // number of days to be read
	$file = 'dracdata.csv';
	$len  = 604; // number of characters in each line of data

	$d0 = new DateTime("1/1/1950");
	$d1 = new DateTime("1/1/1950");
	$d2 = new DateTime("1/1/2051");
	$start = $d0->diff($d1)->format('%a');
	$end   = $d0->diff($d2)->format('%a');

	function getDay( $handle, $line_num, $length ) {
		fseek( $handle, $line_num * $length );
		return json_decode( fgets( $handle, $length ) );
	}

	function deg2sdhm( $degrees ) {
		$signs = [ 'q', 'w', 'e', 'r', 't', 'z', 'u', 'i', 'o', 'p', 'ü', '+' ];
		$sign = $signs[ floor( $degrees / 30 ) ];
		$degrees = fmod( $degrees, 30.0 );
		$deg = str_pad( floor( $degrees ), 2, ' ', STR_PAD_LEFT );
		$hours = ( $degrees - $deg ) * 60;
		$hr = floor( $hours );
		$min = str_pad( round( ( $hours - $hr ) * 60 ), 2, '0', STR_PAD_LEFT);
		$hr = str_pad( $hr, 2, '0', STR_PAD_LEFT );
		return [ 's' => $sign, 'd' => $deg, 'h' => $hr, 'm' => $min ];
	}

	$thead = <<<EOH
	<table class="%s">
		<thead>
			<tr>
				<th colspan="58"><div class="month">%s %s</div>LONGITUDE</th>
			</tr>
			<tr>
				<th scope="col" colspan="2">Day</th>
				<th scope="col" colspan="4" title="Sun">a</th>
				<th scope="col" colspan="4" title="Moon"><span>0 hr</span> s</th>
				<th scope="col" colspan="4" title="Moon"><span>Noon</span> s</th>
				<th scope="col" colspan="4" title="Mercury">d</th>
				<th scope="col" colspan="4" title="Venus">f</th>
				<th scope="col" colspan="4" title="Mars">h</th>
				<th scope="col" colspan="4" title="Juno">D</th>
				<th scope="col" colspan="4" title="Ceres">A</th>
				<th scope="col" colspan="4" title="Jupiter">j</th>
				<th scope="col" colspan="4" title="Saturn">k</th>
				<th scope="col" colspan="4" title="Chiron">l</th>
				<th scope="col" colspan="4" title="Uranus">ö</th>
				<th scope="col" colspan="4" title="Neptune">ä</th>
				<th scope="col" colspan="4" title="Pluto">#</th>
			</tr>
		</thead>
		<tbody>
EOH;
	$blank = <<<EOR
	<tr>
		<td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
		<td>&nbsp;</td><td></td><td>&nbsp;</td><td>&nbsp;</td>
	</tr>
EOR;
?><!DOCTYPE html>
<html>
<head>
	<title>Ephemeris</title>
	<link rel="stylesheet" href="normalize.css">
	<link rel="stylesheet" href="draconic.css" media="print">
	<link rel="stylesheet" href="ephemeris.css" media="screen">
</head>
<body>
	<?php
		$prev   = [];
		$verso  = true; // left-hand page
		$upper  = true; // upper month on the page
		$month  = '';   // the current month
		$count  = 0;
		$fh     = fopen( $file, 'r' ); // open the data file
//		for ( $i = 0; $i < $days; $i++ ) {
		for ( $i = $start; $i < $end; $i++ ) {
			// get the next day's data; extract the day of the month
			$data = getDay( $fh, $i, $len );
			$time = strtotime( $data->date );
			$day  = intval( date( 'j', $time ) );

			// if it is the first day of the month, we need to start a new chart
			if ( 1 == $day ) {
				// do we need to close the previous chart?
				if ( !empty( $month ) ) {
					while ( $count < 36 ) {
						echo $blank;
						$count++;
					}
					echo '</tbody>';
					if ( $upper ) echo '<tfoot><tr><td colspan="58" class="notes">Notes</td></tr></tfoot>';
					echo '</table>';
				}
				// get the new month and year
				$month = date( 'F', $time );
				$year  = intval( date( 'Y', $time ) );
				// start the new table
				$class  = $verso ? 'verso ' : 'recto ';
				$class .= $upper ? 'upper'  : 'lower';
				printf( $thead, $class, $month, $year );
				$upper = $upper ? false : true; // toggle upper/lower
				if ( $upper ) $verso = $verso ? false : true; // toggle recto/verso when upper is true
				$prev   = [];
				$count  = 0;
			}
			// otherwise just output the row with this day's data
			$dow = substr( date( 'D', $time ), 0, 2 );
			$dow = in_array( $dow, [ 'Mo', 'We', 'Fr' ] ) ? substr( $dow, 0, 1 ) : $dow;
			if ( $day > 1 && 'Su' == $dow ) { echo $blank; $count++; }
			echo "<tr><td>$day</td><td>$dow</td>";
			foreach ( $data->planets as $name => $p ) {
				if ( 'node' == $name ) continue;
				$info = deg2sdhm( $p->lon );
				$sign = !empty( $prev[ $name ] ) && $info[ 's' ] === $prev[ $name ] ? ' ' : $info[ 's' ];
				$ret  = $p->ret ? 'ret' : '';
				echo $p->ret ?
					"<td class=\"ret\">{$info['d']}</td><td class=\"ret\">$sign</td><td class=\"ret\">{$info['h']}</td><td class=\"ret\">{$info['m']}</td>":
					"<td>{$info['d']}</td><td>$sign</td><td>{$info['h']}</td><td>{$info['m']}</td>";
				$prev[ $name ] = $info[ 's' ];
			}
			echo '</tr>';
			$count++;
		}
		fclose( $fh );
		// close the last table
		while ( $count < 36 ) {
			echo $blank;
			$count++;
		}
		echo '</tbody><tfoot><tr><td colspan="58" class="notes">Notes</td></tr></tfoot></table>';
	?>
</body>
</html>