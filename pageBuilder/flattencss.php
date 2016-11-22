<?php
/*
 * Flattencss - used to flatten js files into one file
 * 
 */
function FlattenCss($fileName)
{
	$symbolArray = array(';', '*', '/', '+', '-', '=', '{', '}', '[', ']', '(', ')', ' ', ',');
	$tempString = file_get_contents($fileName, false);

	// remove C/C++ style comments
	$tempString = preg_replace('#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|(//.*)#', '', $tempString);
	
	foreach($symbolArray as $symbol)
	{
		// trailing space
		$tempString = str_replace($symbol . ' ', $symbol, $tempString);
		
		// leading space
		$tempString = str_replace(' ' . $symbol, $symbol, $tempString);
	}
	$tempString = str_replace(PHP_EOL, '', $tempString);
	$returnString = str_replace('	', '', $tempString);
	
	return $returnString; 
}
?>