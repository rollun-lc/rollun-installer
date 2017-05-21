<?php

/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 06.01.17
 * Time: 5:27 PM
 */

namespace rollun\test\installer\Install;

use rollun\installer\Install\InstallerAbstract;

class InstallerClass extends InstallerAbstract
{

    function install()
    {
        return ["name" => "value"];
    }

    public function isInstall()
    {
        return false;
    }

    public function uninstall()
    {

    }

    public function getDescription($lang = "en")
    {
        switch ($lang) {
            case "en":
                $description = "en text";
                break;
            case "ru":
                $description = "ru text";
                break;
            default:
                $description = "Does not exist.";
        }
        return $description;
    }

}
