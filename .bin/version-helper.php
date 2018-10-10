<?php

define ("VERSION_FILE", __DIR__ . "/../SHOPVERSIONS");

function generateReleaseVersions($versions) {
    $versionRange = $versions["CUR"];

    if ($versions["MIN"] !== $versions["CUR"]) {
        $versionRange = $versions["MIN"] . " - " . $versions["CUR"];
    }

    return "
        ***Tested Versions:** OpenCart {$versions['CUR']} with PHP 5.6, PHP 7.0 and PHP 7.1*\\n
        ***Compatibility:** OpenCart {$versionRange} with PHP 5.6, PHP 7.0 and PHP 7.1*
    ";
}

// Bail out if we don"t have defined shop versions and throw a loud error.
if (!file_exists(VERSION_FILE)) {
    fwrite(STDERR, "ERROR: No shop version file exists" . PHP_EOL);
    exit(1);
}

$versions = json_decode(
    file_get_contents(VERSION_FILE),
    true
);


echo generateReleaseVersions($versions);