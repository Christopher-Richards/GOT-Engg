<?php
/**
 * GOT-engg : Capstone project for SSE  (https://www.gotengg.org)
 * Copyright (c) Christopher Richards, Jordan Cook
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Christopher Richards, Jordan Cook
 * @link      https://www.gotengg.org SSE Capstone Project
 * @since     Year: 2016
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */


if ((include '/var/www/html/GOTengg/src/Controller/Controller.php') == TRUE) {}


	if ($_SERVER["REQUEST_METHOD"] == "POST"){
		$search= htmlspecialchars($_POST['search']);
		$search = htmlentities($search);
		
		$Results = new SearchClassesController();
		$Results->searchClass($search);
	}


?>