<?php
/**
 * This file is part of OXID eSales PayPal Plus module.
 *
 * OXID eSales PayPal Plus module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal Plus module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal Plus module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 */

// DO NOT TOUCH THIS _ INSTEAD FIX NOTICES - DODGER
error_reporting((E_ALL ^ E_NOTICE) | E_STRICT);
ini_set('display_errors', true);

define ('OXID_PHP_UNIT', true);

$_sOverridenShopBasePath = null;

/**
 * Sets a path to the test shop
 *
 * @deprecated Define OX_BASE_PATH constant instead
 *
 * @param string $sPath New path to shop
 */
function overrideGetShopBasePath($sPath)
{
    //TS2012-06-06
    die("overrideGetShopBasePath() is deprecated use OX_BASE_PATH constant instead. ALWAYS.");
    global $_sOverridenShopBasePath;
    $_sOverridenShopBasePath = $sPath;
}

define('OX_BASE_PATH', isset($_sOverridenShopBasePath) ? $_sOverridenShopBasePath : oxPATH);

/*
function getShopBasePath()
{
    global $_sOverridenShopBasePath;
    if (isset($_sOverridenShopBasePath)) {
        return $_sOverridenShopBasePath;
    }
    return oxPATH;
}*/

function getTestsBasePath()
{
    return realpath(dirname(__FILE__) . '/../');
}

require_once 'test_utils.php';

// Generic utility method file.
require_once OX_BASE_PATH . 'core/oxfunctions.php';

// As in new bootstrap to get db instance.
$oConfigFile = new OxConfigFile(OX_BASE_PATH . "config.inc.php");
\OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\ConfigFile::class, $oConfigFile);
\OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, new oxConfig());

// As in new bootstrap to get db instance.
$oDb = new oxDb();
$oDb->setConfig($oConfigFile);
$oLegacyDb = $oDb->getDb();
\OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\DatabaseProvider::class, $oLegacyDb);

oxRegistry::getConfig();

/**
 * Useful for defining custom time
 */
class modOxUtilsDate extends oxUtilsDate
{

    protected $_sTime = null;

    public static function getInstance()
    {
        return \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\UtilsDate::class);
    }

    public function UNITSetTime($sTime)
    {
        $this->_sTime = $sTime;
    }

    public function getTime()
    {
        if (!is_null($this->_sTime)) {
            return $this->_sTime;
        }

        return parent::getTime();
    }
}

// Utility class
require_once getShopBasePath() . 'core/oxutils.php';

// Database managing class.
require_once getShopBasePath() . 'core/adodblite/adodb.inc.php';

// Session managing class.
require_once getShopBasePath() . 'core/oxsession.php';

// Database session managing class.
// included in session file if needed - require_once( getShopBasePath() . 'core/adodb/session/adodb-session.php');

// DB managing class.
//require_once( getShopBasePath() . 'core/adodb/drivers/adodb-mysql.inc.php');
require_once getShopBasePath() . 'core/oxconfig.php';

function initDbDump()
{
    static $done = false;
    if ($done) {
        throw new Exception("init already done");
    }
    if (file_exists('unit/dbRestore.php')) {
        include_once 'unit/dbRestore.php';
    } else {
        include_once 'dbRestore.php';
    }
    $iTime = microtime(true);

    $dbM = new DbRestore();
    $dbM->dumpDB();

    $done = true;

    return sprintf("%.2f", (microtime(true) - $iTime));
}

echo "\nDB dump time: " . initDbDump() . " seconds\n\n\n";

include_once 'criticalerrorshandler.php';
$oCritical = new CriticalErrorsHandled();