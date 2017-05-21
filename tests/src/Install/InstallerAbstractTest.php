<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 05.01.17
 * Time: 13:58
 */

namespace rollun\test\installer\Install;

use rollun\installer\TestCase\InstallerTestCase;
use rollun\test\installer\Install\InstallerClass;

class InstallerAbstractTest extends InstallerTestCase
{

    public function test_askYesNoQuit()
    {
        $outputStream = $this->getOutputStream();
        $container = $this->getContainer();
        $io = $this->getIo("y\n", $outputStream);
        $installer = new InstallerClass($container, $io);
        $resalt = $installer->askYesNoQuit("Yes or Mo?");
        //var_dump($resalt);
        $this->assertEquals(
                'y', $resalt
        );
        rewind($outputStream);
        $this->assertEquals("Yes or Mo?", stream_get_contents($outputStream));
    }

}
