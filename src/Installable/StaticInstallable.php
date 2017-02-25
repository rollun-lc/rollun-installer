<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 25.02.17
 * Time: 11:38 AM
 */

namespace rollun\installer\Installable;


interface StaticInstallable
{
    /**
     * Make object self install. Non context bind.
     * @param array $option
     * @return array
     */
    static public function Install(array $option = []);

    /**
     * Make self uninstall. Non context bind.
     * @param array $option
     * @return mixed
     */
    static public function Uninstall(array $option = []);

    /**
     * Check if object is installed. Non context bind.
     * @param array $option
     * @return bool
     */
    static public function isInstall(array $option = []);
}