<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use VC4SM\Bundle\Vc4smBundle;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private $vc4smConfig;

    public function __construct(array $vc4smConfig = [])
    {
        $this->vc4smConfig = $vc4smConfig;
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        //yield new SecurityBundle();
        //yield new TwigBundle();
        //yield new NelmioCorsBundle();
        //yield new ApiPlatformBundle();
        yield new Vc4smBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('vc4sm', $this->vc4smConfig);
        });
    }
}
