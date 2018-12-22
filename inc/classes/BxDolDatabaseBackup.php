<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */
define('DOLPHIN_VERSION', $GLOBALS['site']['ver'] . '.' . $GLOBALS['site']['build']);

class BxDolDatabaseBackup extends BxDolDb
{

   var $sCharset,  $sCollate;
   var $sInputs;

   function __construct($sCharset = 'utf8',  $sCollate = 'utf8_unicode_ci')
   {
     $this -> sCharset = $sCharset;
     $this -> sCollate = $sCollate;
     $this -> sInputs  =
'--
-- Database Dump For Dolphin: ' . DOLPHIN_VERSION . '
--

';

   }

    function _getTableStruct($name, $data = 0)
    {
        if ($data != 1) { ## 1 only data
            ##Read table structure
            $Query = "SHOW CREATE TABLE {$name}";
            $Result =  db_res($Query);

            $this -> sInputs .=
"
--
-- Table structure for table `{$name}`
--

DROP TABLE IF EXISTS `{$name}`;
";

            while($Row = $Result->fetch(PDO::FETCH_NUM))
                $this -> sInputs .= preg_replace("/ENGINE=.*/",  "ENGINE=MyISAM DEFAULT CHARSET={$this -> sCharset};\n",  $Row[1]);
        }

        ###	Read data from table
        if ($data != 0)	{ ##Only strucure

            $this -> sInputs .=
"
--
-- Dumping data for table `{$name}`
--

";

            $Query = "SELECT *  FROM {$name} ";
            $Result =  db_res($Query);

            while($Row = $Result->fetch(PDO::FETCH_NUM)) {
                $this -> sInputs .= "INSERT INTO `{$name}` VALUES (";

                for ($j = 0; $j < count($Row); $j++ ) {
                    if( is_null( $Row[$j] ) )
                            $this -> sInputs .= "NULL, ";
                    else //string or numeric
                            $this -> sInputs .= "'" . my_escape_string($Row[$j]) . "', ";
                }

                $this -> sInputs = substr ($this -> sInputs, 0, strrpos($this -> sInputs, ',')); //delete last ,
                $this -> sInputs .= ");\n";
            }
            $this -> sInputs .= "\n-- --------------------------------------------------------\n";
        }
    }

    function _getAllTables($data = false)
    {
        $Query = "SHOW TABLES";
        $Result =  db_res($Query);
        while($Row = $Result->fetch(PDO::FETCH_NUM))
            $this -> _getTableStruct($Row[0], $data);
    }

    function _restoreFromDumpFile($file)
    {
        return execSqlFile( $file );
    }

}

function my_escape_string( $text )
{
    $text = str_replace( '\\', '\\\\', $text );
    $text = str_replace( '\'', '\'\'', $text );
    $text = str_replace( "\n", '\\n', $text );
    $text = str_replace( "\r", '\\r', $text );
    return $text;
}
