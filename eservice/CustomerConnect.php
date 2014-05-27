<?php

//     <document_root>/MyWebSite/CustomerConnect.php
//     Invoked by CostCalculation.php and other sample applications
//     Star Track Express
//     25 May 2011
//     Version 4.3

// THIS FILE IS A PART OF THE SAMPLE CUSTOMER APPLICATIONS AND IS NOT PART OF THE eSERVICES API.
// CUSTOMER-WRITTEN APPLICATIONS MAY INCORPORATE THESE CLASSES FOR CONVENIENCE, MODIFIED AS NECESSARY.

require_once(dirname(__FILE__).'/eServices.php');								// Import the Star Track Express PHP API - do not modify that file

define("USER_INACCESSIBLE_PATH", "/usr/lib/cups/cgi-bin/");		// ****** MODIFY AS REQUIRED ******
															// The directory that will hold WSDL, JSON and properties files
															// For security reasons, must be inaccessible to web users
class SecurePath
{
	public function getSecurePath()
	// Callback class, required by eServices.php
	// Returns path to folder containing files that must not be accessible to web users
	{
		return USER_INACCESSIBLE_PATH;
	}
}

class ConnectDetails
{
    public function getConnectDetails()
	
	// Retrieves connection details from parameter files in document root of website:
	//		Environment.properties
	//		ProductionWSDL.properties
	//		StagingWSDL.properties
	//		Credentials.properties
	// Returns associative array containing parameters required to connect to Star Track Express staging or production eServices.
	
	{
		try
		{
																		// Any directory inaccessible to users			
			// Get location of WSDL

			$environment = file(USER_INACCESSIBLE_PATH . "Environment.properties");		// File should contain either "production" or "staging"
			if (rtrim($environment[0]) == "production")
			{
				$filespec = file(USER_INACCESSIBLE_PATH . "ProductionWSDL.properties");	// Production WDSL filespec					
			}
			else
			{
				$filespec = file(USER_INACCESSIBLE_PATH . "StagingWSDL.properties");		// Staging WSDL filespec				
			}
			$wsdlFilespec = USER_INACCESSIBLE_PATH . rtrim($filespec[0]);
			
			// Securely retrieve credentials from a location inaccessible to users: username, password, user access key.
			// In this simple example we use files in a directory inaccessible to users, however a database could give better protection.
			// NOTE: username, password, user access key and account number must not be visible to users.
			$credentials = file(USER_INACCESSIBLE_PATH . "Credentials.properties");		// Username, password
			$username = rtrim($credentials[0]);
			$password = rtrim($credentials[1]);
			$userAccessKey = rtrim($credentials[2]);
																	
			// Assemble parameters for authentication and SSL connection
			return array(	
							'username' => $username,
							'password' => $password,
							'userAccessKey' => $userAccessKey,
							'wsdlFilespec' => $wsdlFilespec
						);
		}
		catch (Exception $e)
		{
			throw new Exception("Exception in getConnectDetails, " . $e->getMessage(), "\n");
			// It is left to the caller to handle this exception as desired
		}
	}
}

class globalJSONCache
// Callback class for eServices.php
// Used when customer wishes to implement global caching of JSON reference files for performance reasons, 
// using for example Memcached.
// Simply uncomment the function below and fill in the logic. eServices will find the function if it exists.

// Note: Refer to the function getJSONArray in eServices.php

{
/*
	static public function getJSONFileContents($fileSpec)
	// Input parameter: Full file path for a JSON reference file
	// Return value: associative array containing JSON-decoded contents of the file
	{
		... customer code ...
		... customer code ...
		$contents = file_get_contents($fileSpec) or die("Problem with JSON file $fileSpec");
		$valueToStoreInGlobalCache = json_decode($contents, true);
		... customer code ...
		... customer code ...
		return $valueToStoreInGlobalCache;
	}
*/
}

class DynamicHTML
{
	// Dynamic HTML utility class

	public function addColumns($columnCount, array $contentArray)
	{
		// Adds columns to an HTML row
		// columnCount = number of columns to add
		// contentArray = array containing column contents
		$result = "";
		for ($i = 0; $i < $columnCount; $i++)
		{ 
			$result .= "<td>" . $contentArray[$i] . "</td>";
		}
		echo $result;
	}
}

?>
