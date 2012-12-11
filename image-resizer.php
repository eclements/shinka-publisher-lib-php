<?php

if (substr($_GET['url'], 0, 26) !== 'http://ox-i.shinka.sh/bba/') {
	//if ($this->_requestParam_xid == TESTUSER) print 'Not correct URL.<br/>';
    exit;
}

header('Content-Type: image/png');

	//if ($this->_requestParam_xid == TESTUSER) print 'Getting raw file...<br/>';
    $raw = file_get_contents($_GET['url']);

	//if ($this->_requestParam_xid == TESTUSER) print 'Creating image from string...<br/>';
    $source = imagecreatefromstring($raw);

    if ($source !== false) {

		//if ($this->_requestParam_xid == TESTUSER) print 'Getting width and height...<br/>';
		
        $width = $_GET['width'];
        $height = $_GET['height'];
		
		if ((isset($_GET['device'])) && ($_GET['device'] > 0))
		{
			$deviceWidth = $_GET['device'];
		}
		else
		{
			$deviceWidth = 120;
		}
		
		//print 'Banner Width: ' . $width . '<br/>';
		//print 'Banner Height: ' . $height . '<br/>';		
		//print 'DeviceWidth: ' . $_GET['device'] . '<br/>';	

        if ($deviceWidth > ($width+10)) {
            $newwidth = ($deviceWidth-10);
        } else {
            $newwidth = $deviceWidth;
        }
        $newheight = (($newwidth / $_GET['width']) * $_GET['height']);
		
		//print 'New width: ' . $newwidth . '<br/>';
		//print 'New height: ' . $newheight . '<br/>';

        $output = imagecreatetruecolor($newwidth, $newheight);

        imagecopyresampled($output, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    }

    ob_start();
    imagepng($output, null, 0);
    $stringdata = ob_get_contents();
    ob_end_clean();

echo $stringdata;
