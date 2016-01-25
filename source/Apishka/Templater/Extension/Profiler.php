<?php

/*
 * This file is part of Twig.
 *
 * (c) 2015 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Extension_Profiler extends Apishka_Templater_Extension
{
    private $actives = array();

    public function __construct(Apishka_Templater_Profiler_Profile $profile)
    {
        $this->actives[] = $profile;
    }

    public function enter(Apishka_Templater_Profiler_Profile $profile)
    {
        $this->actives[0]->addProfile($profile);
        array_unshift($this->actives, $profile);
    }

    public function leave(Apishka_Templater_Profiler_Profile $profile)
    {
        $profile->leave();
        array_shift($this->actives);

        if (1 === count($this->actives)) {
            $this->actives[0]->leave();
        }
    }

    public function getNodeVisitors()
    {
        return array(new Apishka_Templater_Profiler_NodeVisitor_Profiler($this->getName()));
    }

    public function getName()
    {
        return 'profiler';
    }
}
