<?php

namespace Karser\Recaptcha3Bundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

final class IpResolver implements IpResolverInterface
{
    /** @var KernelInterface */
    private $kernel;

    /** @var Request */
    private $request;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->request = $this->getRequest();
    }

    public function getRequest()
    {
        if ($this->kernel->getContainer()->has('request')) {
            $request = $this->kernel->getContainer()->get('request');
        } else {
            $request = Request::createFromGlobals();
        }
        return $request;
    }

    public function resolveIp()
    {
        $request = $this->request;
        if ($request === null) {
            return null;
        }
        return $request->getClientIp();
    }
}
