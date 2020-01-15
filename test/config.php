<?php

/**
 * Sample configuration file for Anax webroot.
 */


/**
 * Define essential Anax paths, end with /
 */
define("ANAX_INSTALL_PATH", realpath(__DIR__ . "/.."));
define("TEST_INSTALL_PATH", __DIR__);

/**
 * Include autoloader.
 */
require ANAX_INSTALL_PATH . "/vendor/autoload.php";



/**
 * Use $di as global identifier (used in views by viewhelpers).
 */
$di = null;


/**
 * Boostrapping code for database, as its used in memory
 */


/**
 * Simple function for making .sql files readable into PDO statements
 * @param string $sql SQL statements to parse
 *
 * @return array
 */
function prepareSQL(string $sql): array
{
    $defaultDelimiter = ";";
    $insert = "INSERT INTO";
    // Special delimiters
    $delimiters = [
        "CREATE TRIGGER" => "END;"
    ];

    $stmts = [ "" ];
    $lines = explode("\n", $sql);
    $delimiter = $defaultDelimiter;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || substr($line, 0, 2) == "--") {
            continue;
        }

        // Check if we need another delimiter than the default one
        foreach ($delimiters as $key => $delim) {
            if (substr($line, 0, strlen($key)) == $key) {
                $delimiter = $delim;
            }
        }

        // Append line to current statement
        $stmt = array_pop($stmts) . "$line\n";
        $stmts[] = $stmt;

        if (substr($stmt, 0, strlen($insert)) == $insert) {
            $openCount = substr_count($stmt, "(");
            $closeCount = substr_count($stmt, ")");
            // IF ; is within unstable amount of parenthesis, continue.
            if ($openCount != $closeCount) {
                continue;
            }
        }

        // If line ends with current delimiter, finish the statement
        if (substr($line, strlen($line) - strlen($delimiter)) == $delimiter) {
            $stmts[] = "";
            $delimiter = $defaultDelimiter;
        }
    }

    return array_slice($stmts, 0, -1);
}

function executeSQLFile(string $file): void
{
    global $di;

    $di->dbqb->connect();
    $sql = file_get_contents(ANAX_INSTALL_PATH . "/sql/$file.sql");
    $stmts = prepareSQL($sql);

    foreach ($stmts as $stmt) {
        $di->dbqb->execute($stmt);
    }
}


/**
 * Creates all tables with triggers
 *
 * @return void
 */
function createTestDatabase(): void
{
    executeSQLFile("ddl");
    executeSQLFile("triggers");
}


/**
 * Inserts data into the database
 *
 * @return void
 */
function populateTestDatabase(): void
{
    executeSQLFile("dml");
}
