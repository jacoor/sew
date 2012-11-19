<?php

include_once 'Image/Tools.php';
include_once 'Image/Tools/Thumbnail.php';

/**
 * Nowy THUMB
 * Parametry:
 * width - szerokość
 * height - wysokość
 * file - plik żródłowy
 * img - czy ma zwrócić tylko ścieżkę do pliku
 * background - kolor tła w formacie #ffffff
 * type - typ pliku [0 - bez zmian, 1 - gif, 2 - jpg, 3 - png]
 * quality - jakość przy zapisie do jpg
 * assign - przypisanie do zmiennej wyniku
 * method - metoda obróbki obrazka [0 - SCALE_MAX,1 - SCALE_MIN,2 - SCALE_CROP, 3 - SCALE_MAX_AND_SAVE_SIZE ]
 */

function smarty_function_thumb2($params, &$smarty)
{
	$types = array('', '.gif', '.jpg', '.png');
	$cache = empty($params['cache']) ? 'cache/images/' : $params['cache'];

	if (!empty($params['file']) && !@file_exists($params['file']) && $params['file'][0] == '/')
	{
		$params['file'] = $_SERVER['DOCUMENT_ROOT'] . $params['file'];
	}

	if (empty($params['file']))
	{
		$smarty->_trigger_fatal_error("thumb: parameter 'file' cannot be empty and must exist");
		return;
	}

	if (!file_exists($params['file']))
	{
		return '';
	}

	$temp = getimagesize($params['file']);

	$params['type'] = !empty($params['type']) ? $params['type'] : $temp[2];

	$width = $temp[0];
	$height = $temp[1];
	$modified = filemtime($params['file']);

	if(empty($params['method']))
	{
		$params['method'] = 3;
	}
	$params['quality'] = empty($params['quality']) ? 75 : $params['quality'];

	if(!empty($params['height']) || !empty($params['width']))
	{
		if(empty($params['height']))
		{
			$params['height'] = (int) round($height * $params['width'] / $width);
		}
		elseif(empty($params['width']))
		{
			$params['width'] = (int) round($width * $params['height'] / $height);
		}
	}
	else
	{
		$params['width'] = 100;
		$params['height'] = 100;
	}
	$data = array
	(
		'image' => $params['file'],
		'width' => $params['width'],
		'height' => $params['height'],
		'method' => $params['method']
	);

	if(!empty($params['background']))
	{
		$data['background'] = $params['background'];
	}

	$hash = md5($modified.implode('-',$data));

	$output_file = $cache.$hash.$types[$params['type']];

	if(!empty($params['src']) && $params['src'])
	{
		$return = '/'.$output_file;
	}
	else
	{
		$html = array
		(
			"src=\"/$output_file\"",
			"width=\"{$data['width']}\"",
			"height=\"{$data['height']}\""
		);
		$attributes = array('class','id','alt','onclick','onerror','onload','onmousein','onmouseout','onmouseover','onmousemove');
		$params['alt'] = isset($params['alt']) ? $params['alt'] : '';
		foreach($attributes as $a)
		{
			if(isset($params[$a]))
				$html[$a] = "$a=\"$params[$a]\"";
		}
		$html = implode(' ',$html);
		$return = "<img $html/>";
	}
	if (!file_exists(SITE_ROOT .$output_file))
	{
		$thumb = Image_Tools::factory('Thumbnail',$data);
		$thumb->save(SITE_ROOT . $output_file, $params['type'], false, $params['quality']);
	}
	if (!empty($params['assign']))
	{
		$smarty->assign($params['assign'], $return);
		return;
	}
	else
	{
		return $return;
	}
}

?>