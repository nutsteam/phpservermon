<?php
include 'config.php';

if(php_sapi_name() == 'cli' && basename($_SERVER["SCRIPT_NAME"]) == basename(__FILE__)) {
    $dir = dirname(__FILE__);
    echo `$dir/vendor/bin/phinx migrate`;
}

return array(
    "paths" => array(
        "migrations" => "docs/migrations"
    ),
    "environments" => array(
        "default_migration_table" => "phinx_logs",
        "default_database" => "dev",
        "dev" => array(
            "adapter" => "mysql",
            "charset" => "utf8",
            "host" => PSM_DB_HOST,
            "name" => PSM_DB_NAME,
            "user" => PSM_DB_USER,
            "pass" => PSM_DB_PASS,
            "port" => 3306
        )
    )
);