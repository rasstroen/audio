<?php

function valid_email_address($mail) {
	$user = '[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+';
	$domain = '(?:(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.?)+';
	$ipv4 = '[0-9]{1,3}(\.[0-9]{1,3}){3}';
	$ipv6 = '[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}';
	return preg_match("/^$user@($domain|(\[($ipv4|$ipv6)\]))$/", $mail);
}

/**
 * Функция для дебага в браузере
 * @param $smth - что выводить
 * @param boolean $stop - остановить скрипт после вывода
 */
function dbg($smth, $stop = false) {
	echo "<xmp>";
	print_r($smth);
	echo "</xmp>";

	if (false !== $stop) {
		exit;
	}
}

/**
 * Функция создания квадратного изображения с кадрированием.
 * @param string $sourceFile - путь до исходного файла
 * @param string $destinationFile - путь файла, в который сохраняется результат
 * @param integer $width
 * @param integer $height
 * @return
 */
function resizeImagick($sourceFile, $destinationFile, $width, $height, $resultType = 'gif') {
	$info = getimagesize($sourceFile);

	$destinationFile = $destinationFile;


	if (false === in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG)))
		return false;

	$originalWidth = $info[0];
	$originalHeight = $info[1];

	$ratio_orig = $originalWidth / $originalHeight;

	if ($width / $height > $ratio_orig) {
		$width = round($height * $ratio_orig);
	} else {
		$height = round($width / $ratio_orig);
	}

	if ($originalWidth < $width) {
		$height = $originalHeight;
		$width = $originalWidth;
	}

	$newWidth = $width;
	$newHeight = $height;

	$biggestSideSize = max($newWidth, $newHeight);

# создаём новый пустой объект
	$newFileObj = new \Imagick();
# оригинальное изображение
	$im = new \Imagick($sourceFile);

	switch ($info[2]) {
		case IMAGETYPE_GIF:
			$im->setFormat("gif");
			foreach ($im as $animation) {
				$animation->thumbnailImage($newWidth, $newHeight); //Выполняется resize до 200 пикселей поширине и сколько получится по высоте (с соблюдением пропорций конечно) 
				$animation->setImagePage($animation->getImageWidth(), $animation->getImageHeight(), 0, 0);
			}
			$im->writeImages($destinationFile, true);
			return image_type_to_extension($info[2], false);
			break;

		case IMAGETYPE_PNG:
			$im = $im->coalesceImages();
			$im->setFormat("gif");
			$im->thumbnailImage($newWidth, $newHeight);
			$im->writeImages($destinationFile, true);
			return image_type_to_extension($info[2], false);
			break;

		case IMAGETYPE_JPEG:
			$im = $im->coalesceImages();
			$im->setFormat("gif");
			$im->thumbnailImage($newWidth, $newHeight);
			$im->writeImages($destinationFile, true);
			return image_type_to_extension($info[2], false);
			break;
		default :
			die($info[2].'d');
	}
}