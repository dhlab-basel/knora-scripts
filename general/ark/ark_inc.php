<?php

define('ARKSTR', 'ark:/72163/%s');

function bchexdec($hex)
{
    $dec = 0;
    $len = strlen($hex);
    for ($i = 1; $i <= $len; $i++) {
        $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
    }
    return $dec;
}
//==============================================================================

function bcdechex($dec) {
    $hex = '';
    do {    
        $last = bcmod($dec, 16);
        $hex = dechex($last).$hex;
        $dec = bcdiv(bcsub($dec, $last), 16);
    } while($dec>0);
    return $hex;
}
//==============================================================================

class Luhn {

	private static $toCodepoint = [
		'0' => 0,
		'1' => 1,
		'2' => 2,
		'3' => 3,
		'4' => 4,
		'5' => 5,
		'6' => 6,
		'7' => 7,
		'8' => 8,
		'9' => 9,
		'a' => 10,
		'A' => 10,
		'b' => 11,
		'B' => 11,
		'c' => 12,
		'C' => 12,
		'd' => 13,
		'D' => 13,
		'e' => 14,
		'E' => 14,
		'f' => 15,
		'F' => 15
	];

	private static $toCharacter = [
		0 => '0',
		1 => '1',
		2 => '2',
		3 => '3',
		4 => '4',
		5 => '5',
		6 => '6',
		7 => '7',
		8 => '8',
		9 => '9',
		10 => 'a',
		11 => 'b',
		12 => 'c',
		13 => 'd',
		14 => 'e',
		15 => 'f'
	];

	private static function basicluhn($hexstr, $n) {
		$factor = 2;
		$sum = 0;
	
		for ($i = strlen($hexstr) - 1; $i >= 0; $i--) {
			if ($hexstr[$i] == '-') continue;
			$addend = $factor * self::$toCodepoint[$hexstr[$i]];
			$factor = ($factor == 2) ? 1 : 2;
			$addend = intval($addend / $n) + ($addend % $n);
			$sum += $addend;
		}
		return $sum;
	}
	
	public static function checksum($hexstr) {
		$sum = self::basicluhn($hexstr, 16);
		$remainder = $sum % 16;
	
		return self::$toCharacter[(16 - $remainder) % 16];
	}
	
	public static function check($hexstr) {
		echo '===', $hexstr, '===', PHP_EOL;
		$sum = self::basicluhn($hexstr, 16);
		$remainder = $sum % 16;
		return ($remainder == 0);
	}
}
//==============================================================================


function ArkId_FromResId($proj_id, $id) {
	$tmp = bcmul('982451653', (string) (intval($id) + 1), 0);
	$tmp = bcdechex($tmp);
	$tmp = sprintf('%04x', 0x800 + intval($proj_id)) . '-' . $tmp;
	return $tmp . '-' . luhn::checksum($tmp);
}
//==============================================================================



function generate_arkid($project_id, $res_id) {
	return sprintf(ARKSTR, ArkId_FromResId($project_id, $res_id));
}

function parse_arkid($arkurl) {
	$arkurl = str_replace('-', '', $arkurl);
	$parts = explode('/', trim($arkurl, "/ \t\n\r\0\x0B"));
	
	$arkdata = new stdClass();
	
	if (($parts[3] == 'ark:') && ($parts[4] == '72163')) { // this must be true
		$subparts = explode('.', $parts[5]);
		$project_id = hexdec(substr($subparts[0], 0, 4));
		if ($project_id < 0x800) { // it's in Lausanne
			
		}
		else {
			$project_id -= 0x800;
		}
		$idstr = substr($subparts[0], 4, strlen($subparts[0]) - 5);
		$date = (count($subparts) > 1) ? $subparts[count($subparts) - 1] : NULL;
		
		if (!luhn::check($subparts[0])) {
			$arkdata->status = FALSE;
			$arkdata->message = 'ARK-identifier invalid – check spelling!';			
		}
				
		$idstr = bchexdec($idstr);
		$idstr = bcdiv($idstr, '982451653');
		$res_id = intval($idstr) - 1;
		
		$arkdata->status = TRUE;
		$arkdata->message = 'OK';
		$arkdata->dns = $parts[2];
		$arkdata->project_id = $project_id;
		$arkdata->res_id = $res_id;
		$arkdata->date = $date;
	}
	else {
		$arkdata->status = FALSE;
		$arkdata->message = 'No ARK-identifier in URL!';
	}
	return $arkdata;
}
