<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Profiler_Dumper_TextTest extends Apishka_Templater_Tests_Profiler_Dumper_AbstractTest
{
    public function testDump()
    {
        $dumper = new Apishka_Templater_Profiler_Dumper_Text();
        $this->assertStringMatchesFormat(<<<EOF
main %d.%dms/%d%
└ index.twig %d.%dms/%d%
  └ embedded.twig::block(body)
  └ embedded.twig
  │ └ included.twig
  └ index.twig::macro(foo)
  └ embedded.twig
    └ included.twig

EOF
        , $dumper->dump($this->getProfile()));
    }
}
