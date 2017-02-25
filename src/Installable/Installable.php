<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 25.02.17
 * Time: 11:38 AM
 */

namespace rollun\installer\Installable;


interface Installable
{
    /**
     * Make object self install
     * @param array $option
     * @return array
     */
    public function Install(array $option = []);


    /**
     * Make self uninstall.
     * @param array $option
     * @return mixed
     */
    public function Uninstall(array $option = []);


    /**
     * Check if object is installed.
     * @param array $option
     * @return bool
     */
    public function isInstall(array $option = []);
}