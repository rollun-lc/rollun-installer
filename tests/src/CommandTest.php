<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 05.01.17
 * Time: 13:58
 */

namespace rollun\test\installer;

use rollun\installer\InstallerCommand;

class CommandTest extends \PHPUnit\Framework\TestCase
{

    public function testPublicDir()
    {
        $expectedPublicDir = realpath('public');
        $publicDir = InstallerCommand::getPublicDir();
        $this->assertEquals($expectedPublicDir, $publicDir);
    }

}
