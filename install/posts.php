<?php
include_once("../sitesetup.php");
// configure our site first as we are installing
//setupSite(true);

include_once("../pageBuilder/xmlPage.php");
include_once("../toolbox/filetools.php");
include_once '../toolbox/usertools.php';


define('DB_CONNECT_FAIL', 'Unable to connect to the database.');
define('DB_CREATE_FAIL', 'Unable to create the selected table - posts.');
define('DB_SUCCESS', 'Successfully created posts table.');
define('DB_TABLE_ENTRY_FAIL', 'Unable to set default data into this table');
define('DB_EXISTS', 'The requested table (posts) already exists.  Replace it?');

define('TABLE_DESCRIPTION', 'A table for all posts for this site');

function testPostsTable($replaceTable)
{
	$systemObject = getSystemObject();
	
	$dbName = $systemObject->getConfigurationData(SITE_DB_NAME);
	$dbInstance = $systemObject->getDbInstance();
	  
  $returnString = "None.";

  /* Create a database and make all good with the world */
  if ($dbInstance != null)
  {
    $dbExists = $dbInstance->selectDatabase($dbName);

    /* Ok if db exists, see if the table exists */
    if( $dbExists == true)
    {
      $tableExists = $dbInstance->doesTableExist(TABLE_NAME);
      if ($tableExists == false)
      {
        $returnString = "Could not find it";
      }
      else
      {
        $returnString = "Table exists!";
      }
    }
  }
  return $returnString;
}

function createPostsTable($replaceTable)
{
	$systemObject = getSystemObject();
	
	$dbName = $systemObject->getConfigurationData(SITE_DB_NAME);
	$dbInstance = $systemObject->getDbInstance();
	  
  $returnString = "None.";

    /* Create a database and make all good with the world */
  if ($dbInstance != null)
  {
    $dbExists = $dbInstance->selectDatabase($dbName);

    /* Ok if db exists, see if the table exists */
    if( $dbExists == true)
    {
      $tableExists = $dbInstance->doesTableExist(TABLE_NAME);
      if (($tableExists == false) || ($replaceTable == true))
      {
        /* If the table exists and we are here then they must want to
           replace it */
        if ($tableExists == true)
        {
          $dbInstance->dropTable(TABLE_NAME);
        }

        $tableColumns = array();
        $tableColumns[0] = new DBTableColumn();
        $tableColumns[0]->setColumnName("id");
        $tableColumns[0]->setColumnType("int");
        $tableColumns[0]->setTypeLength(11);
        $tableColumns[0]->setAutoIncrement(true);
        $tableColumns[0]->setPrimaryKey(true);

        $tableColumns[1] = new DBTableColumn();
        $tableColumns[1]->setColumnName("contentId");
        $tableColumns[1]->setColumnType("int");
        $tableColumns[1]->setTypeLength(11);
        $tableColumns[1]->setAutoIncrement(false);
        
        $tableColumns[2] = new DBTableColumn();
        $tableColumns[2]->setColumnName("data");
        $tableColumns[2]->setColumnType("varchar");
        $tableColumns[2]->setTypeLength(1024);
        $tableColumns[2]->setAllowNull(false);
        
        $tableColumns[3] = new DBTableColumn();
        $tableColumns[3]->setColumnName("active");
        $tableColumns[3]->setColumnType("BIT");
        $tableColumns[3]->setTypeLength(1);
        $tableColumns[3]->setAllowNull(true);
        $tableColumns[3]->setDefaultValue("b'0'");
        
        $tableColumns[4] = new DBTableColumn();
        $tableColumns[4]->setColumnName("postTimeStamp");
        $tableColumns[4]->setColumnType("TIMESTAMP");
        $tableColumns[4]->setAllowNull(false);
      
        /* Create the table, the prefix will be added by the create call */
        if ($dbInstance->createTable(TABLE_NAME, $tableColumns, TABLE_DESCRIPTION) == true)
        {
        	/* Add the default content to the home page */
        	$queryString = "insert into " . $systemObject->getConfigurationData(SITE_TABLE_PREFIX);
        	$queryString .= TABLE_NAME . "(`contentId`, `data`, `active`, `postTimeStamp`) values ('1', 'Your first post', b'1', CURRENT_TIMESTAMP);";
        	$dbInstance->issueCommand($queryString);
        	
	        $returnString = DB_SUCCESS;	        
        }
        else
        {
          $returnString = DB_CREATE_FAIL;
        }
      }
      else
      {
        $returnString = DB_EXISTS;
      }
    }
    else
    {
      $returnString = DB_CONNECT_FAIL;
    }
  }
  else
  {
    $returnString = DB_CONNECT_FAIL;
  }

  return $returnString;
}

/* Process create request */

/* Default to not replace if the table already exists */
$replace = false;

if (isset($_GET['replace']) == true)
{
  if ($_GET['replace'] == "true")
  {
    $replace = true;
  }
}

if (isset($_GET['tableName']) == true)
{
	define('TABLE_NAME', $_GET['tableName']);
}
else
{
	define('TABLE_NAME', 'posts');
}
$tableCreateStatus = createPostsTable($replace);
/*$tableCreateStatus = testPostsTable($replace);*/

/* Create our xml response - default to direct display */
$xmlPage = new XmlPageData();
$xmlPage->setName('utility');

$xmlNode = new XmlDataObject();
$xmlNode->setName('results');
if ($tableCreateStatus != DB_SUCCESS)
{
  if ($tableCreateStatus == DB_EXISTS)
  {
    $xmlNode->setValue('-1');
  }
  else
  {
    $xmlNode->setValue('0');
  }
}
else
{
  $xmlNode->setValue('1');
}
$xmlPage->addChildObject($xmlNode);

$xmlNode = new XmlDataObject();
$xmlNode->setName('returnString');
$xmlNode->setValue($tableCreateStatus);
$xmlPage->addChildObject($xmlNode);

$xmlPage->renderPage();
?>