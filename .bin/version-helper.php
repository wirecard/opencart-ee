<?php

define ("REPO", getenv("TRAVIS_REPO_SLUG"));
define ("REPO_NAME", explode("/", REPO)[1]);
define ("SCRIPT_DIR", __DIR__);
define ("WIKI_DIR", SCRIPT_DIR . "/../" . REPO_NAME . ".wiki");
define ("WIKI_FILE", WIKI_DIR . "/Home.md");
define ("README_FILE", SCRIPT_DIR . "/../README.md");
define ("VERSION_FILE", SCRIPT_DIR . "/../SHOPVERSIONS");
define ("TRAVIS_FILE", SCRIPT_DIR . "/../.travis.yml");

require SCRIPT_DIR . "/../system/library/autoload.php";

use Symfony\Component\Yaml\Yaml;

// Using number_format on versions feels wrong, but symfony/yaml insists on returning the versions as float.
// This just guarantees that we have versions like 7 returned as "PHP 7.0" for added clarity.
function prefixWithPhp($version) {
    return "PHP " . number_format($version, 1);
}

// Simple function to allow us to join arrays as "x, y and z"
function naturalLanguageJoin($list, $conjunction = 'and') {
    $last = array_pop($list);

    if ($list) {
        return implode(', ', $list) . ' ' . $conjunction . ' ' . $last;
    }

    return $last;
}

// Generates the version strings for the text.
function makeTextVersions($shopVersions, $phpVersions) {
    $versionRange = $shopVersions["tested"];
    $phpVersions = array_map("prefixWithPhp", $phpVersions);
    $phpVersionString = naturalLanguageJoin($phpVersions);

    if ($shopVersions["compatibility"] !== $shopVersions["tested"]) {
        $versionRange = $shopVersions["compatibility"] . " - " . $shopVersions["tested"];
    }

    return [
        "versionRange" => $versionRange,
        "phpVersionString" => $phpVersionString
    ];
}

// Makes the actual release version string. Magic!
function generateReleaseVersions($shopVersions, $phpVersions) {
    $releaseVersions = makeTextVersions($shopVersions, $phpVersions);
    return "***Tested version(s):** {$shopVersions['shopsystem']} {$shopVersions['tested']} with {$releaseVersions['phpVersionString']}*<br>***Compatibility:** {$shopVersions['shopsystem']} {$releaseVersions['versionRange']} with {$releaseVersions['phpVersionString']}*";
}

// Doing some regex replacement for the wiki
function generateWikiRelease($shopVersions, $phpVersions) {
    if (!file_exists(WIKI_FILE )) {
        fwrite(STDERR, "ERROR: Wiki files do not exist." . PHP_EOL);
        exit(1);
    }

    $wikiPage = file_get_contents(WIKI_FILE);
    $releaseDate = date("Y-m-d");
    $releaseVersions = makeTextVersions($shopVersions, $phpVersions);

    $testedRegex = "/^\|\s?\*.?Tested.*\|(.*)\|/mi";
    $compatibilityRegex = "/^\|\s?\*.?Compatibility.*\|(.*)\|/mi";
    $extVersionRegex = "/^\|\s?\*.?Extension.*\|(.*)\|/mi";

    $testedReplace = "| **Tested version(s):** | {$shopVersions['shopsystem']} {$shopVersions['tested']} with {$releaseVersions['phpVersionString']} |";
    $compatibilityReplace = "| **Compatibility:** | {$shopVersions['shopsystem']} {$releaseVersions['versionRange']} with {$releaseVersions['phpVersionString']} |";
    $extVersionReplace = "| **Extension version** | ![Release](https://img.shields.io/github/release/" . REPO . ".png?nolink \"Release\") ({$releaseDate}), [change log](https://github.com/" . REPO . "/releases) |";

    $wikiPage = preg_replace($testedRegex, $testedReplace, $wikiPage);
    $wikiPage = preg_replace($compatibilityRegex, $compatibilityReplace, $wikiPage);
    $wikiPage = preg_replace($extVersionRegex, $extVersionReplace, $wikiPage);

    file_put_contents(WIKI_FILE, $wikiPage);
}

// Doing the regex replacement for the wiki
function generateReadmeReleaseBadge($shopVersions, $phpVersions) {
    if (!file_exists(README_FILE )) {
        fwrite(STDERR, "ERROR: README file does not exist." . PHP_EOL);
        exit(1);
    }

    $readmeContent = file_get_contents(README_FILE);

    $shopBadge = $shopVersions['shopsystem'] . " v" . $shopVersions['tested'];
    $shopBadgeUrl = str_replace(" ", "-", $shopBadge);

    $badgeRegex = "/\[\!\[{$shopVersions['shopsystem']}.*\]/mi";
    $badgeReplace = "[![{$shopBadge}](https://img.shields.io/badge/{$shopBadgeUrl}-green.svg)]";

    $readmeContent = preg_replace($badgeRegex, $badgeReplace, $readmeContent);

    file_put_contents(README_FILE, $readmeContent);
}

// Bail out if we don"t have defined shop versions and throw a loud error.
if (!file_exists(VERSION_FILE)) {
    fwrite(STDERR, "ERROR: No shop version file exists" . PHP_EOL);
    exit(1);
}

// Get the minimum/maximum compatibility from our SHOPVERSIONS file.
$shopVersions = json_decode(
    file_get_contents(VERSION_FILE),
    true
);

// Grab the Travis config for parsing the supported PHP versions
$travisConfig = Yaml::parseFile(TRAVIS_FILE);
$phpVersions = $travisConfig['php'];

$options = getopt('wr');

// For some reason php returns an options array where 'w' is false when you passed 'w' as argument.
// It makes no sense, but we can deal with it.
if (key_exists('w', $options)) {
    generateWikiRelease($shopVersions, $phpVersions);
    exit(0);
}

// Same same but different
if (key_exists('r', $options)) {
    generateReadmeReleaseBadge($shopVersions, $phpVersions);
    exit(0);
}

echo generateReleaseVersions($shopVersions, $phpVersions);