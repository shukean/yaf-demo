define("YK_ON_CLI_MODE", 1);
if (php_sapi_name() != "cli") {
    exit;
}
include __DIR__.'/index.php'
