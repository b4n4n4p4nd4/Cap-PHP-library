<?php 
/*
 *  Copyright (c) 2015  Niklas Spanring   <n.spanring@backbone.co.at>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file      	index.php
 *  \ingroup   	main
 */
 
/**
 * Front end of the Cap-php-library
 */
	
	require_once 'class/cap.form.class.php';
	require_once 'lib/cap.create.class.php';
	require_once 'lib/cap.write.class.php';
	require_once 'lib/cap.convert.class.php';
	require_once 'class/translate.class.php';
	
	chown($path, $user_name);
	
	if(file_exists('source/conf/conf.php'))
	{
		include 'source/conf/conf.php';
	
		$langs = new Translate();		
		$langs->setDefaultLang($conf->user->lang);		
		$langs->load("main");	
	}
	
	if(!file_exists('source/conf/conf.php'))
	{
		$cap = new CAP_Form();			
		print $cap->install();
	}
	elseif($_GET['conv'] == 1)
	{
		/*
			$tmpfile = $_FILES["uploadfile"]["tmp_name"];   // temp filename
   		$filename = $_FILES["uploadfile"]["name"];      // Original filename

  		$handle = fopen($tmpfile, "r");                  // Open the temp file
   		$contents = fread($handle, filesize($tmpfile));  // Read the temp file
   		fclose($handle);                                 // Close the temp file
		*/
		if(! empty($_POST['location']) || ! empty($_FILES["uploadfile"]["name"]))
		{
			require_once 'lib/cap.read.class.php';
			// Get TEST Cap
			if(! empty($_FILES["uploadfile"]["name"]))
			{
				$location = $_FILES["uploadfile"]["tmp_name"];
			}
			else
			{
				$location = $conf->cap->output.'/'.urldecode($_POST['location']);
			}
			
			$alert = new alert($location);
			$cap = $alert->output();
			
			// Convert
			$converter = new Convert_CAP_Class();		
			print $converter->convert($cap, $_POST['inputconverter'], $_POST['outputconverter']);
		}
		else
		{
			$form = new CAP_Form();
			print $form->ListCap();
		}
	}
	elseif($_GET['read'] == 1)
	{
		require_once 'lib/cap.read.class.php';
		
		$location = "source/cap/2.49.0.20.0.AT.151005121527.91.cap";
		$alert = new alert($location);
		$cap = $alert->output();
		//die(print_r($cap));
		print_r($cap);
		
			$form = new CAP_Form($cap);
			//print $form->Debug();
			print $form->Form();
	}
	elseif(empty($_POST['action']) && $_GET['webservice'] != 1)
	{
		// Build Cap Creator form
		
			$form = new CAP_Form();

			print $form->Form();
			
	}
	elseif($_POST['action'] == "create" && $_GET['conf'] != 1)
	{
		$form = new CAP_Form();
		$_POST = $form->MakeIdentifier($_POST);
		
		$cap = new CAP_Class($_POST);
		
		if(!empty($_GET['cap']))
		{
			// Used for the Cap preview
			$cap->buildCap();
			print $cap->cap;
		}
		else
		{
			// Used to build the cap and save it at $cap->destination
			$cap->buildCap();
			if($conf->cap->save == 1)	$path = $cap->createFile();
			
			$conf->identifier->ID_ID++;
			$form->WriteConf();
			
			print $form->CapView($cap->cap, $_POST[identifier]); // Cap Preview +
		}
	}
	elseif($_GET['webservice'] == 1)
	{
		// start webservices
			$form = new CAP_Form();

			print $form->Webservice($_POST[filename]);
	}
	elseif($_GET['conf'] == "1")
	{
		$form = new CAP_Form();		
		$form->PostToConf($_POST['conf']);		
		$form->WriteConf();
		return true;
	}
	
?>