<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Apishka_Templater_Extension_Sandbox extends Apishka_Templater_Extension
{
    private $sandboxedGlobally;
    private $sandboxed;
    private $policy;

    public function __construct(Apishka_Templater_Sandbox_SecurityPolicyInterface $policy, $sandboxed = false)
    {
        $this->policy = $policy;
        $this->sandboxedGlobally = $sandboxed;
    }

    public function getTokenParsers()
    {
        return array(new Apishka_Templater_TokenParser_Sandbox());
    }

    public function getNodeVisitors()
    {
        return array(new Apishka_Templater_NodeVisitor_Sandbox());
    }

    public function enableSandbox()
    {
        $this->sandboxed = true;
    }

    public function disableSandbox()
    {
        $this->sandboxed = false;
    }

    public function isSandboxed()
    {
        return $this->sandboxedGlobally || $this->sandboxed;
    }

    public function isSandboxedGlobally()
    {
        return $this->sandboxedGlobally;
    }

    public function setSecurityPolicy(Apishka_Templater_Sandbox_SecurityPolicyInterface $policy)
    {
        $this->policy = $policy;
    }

    public function getSecurityPolicy()
    {
        return $this->policy;
    }

    public function checkSecurity($tags, $filters, $functions)
    {
        if ($this->isSandboxed()) {
            $this->policy->checkSecurity($tags, $filters, $functions);
        }
    }

    public function checkMethodAllowed($obj, $method)
    {
        if ($this->isSandboxed()) {
            $this->policy->checkMethodAllowed($obj, $method);
        }
    }

    public function checkPropertyAllowed($obj, $method)
    {
        if ($this->isSandboxed()) {
            $this->policy->checkPropertyAllowed($obj, $method);
        }
    }

    public function ensureToStringAllowed($obj)
    {
        if ($this->isSandboxed() && is_object($obj)) {
            $this->policy->checkMethodAllowed($obj, '__toString');
        }

        return $obj;
    }

    public function getName()
    {
        return 'sandbox';
    }
}
