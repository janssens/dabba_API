<?php
// src/Serializer/ImageNormalizer.php

namespace App\Serializer;

use App\Entity\Cms;
use App\Entity\Restaurant;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ImageNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'IMAGE_NORMALIZER_ALREADY_CALLED';

    private $router;
    private $cacheManager;

    public function __construct(UrlGeneratorInterface $router, CacheManager $cacheManager)
    {
        $this->router = $router;
        $this->cacheManager = $cacheManager;
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        if ($object->getImage()){
            $class = 'default';
            if ($object instanceof Cms){
                $class = 'cms';
                $object->setImage($this->router->generate('app_home',[],UrlGeneratorInterface::ABSOLUTE_URL).'uploads/images/'.$class.'/'.$object->getImage());
            }
            if ($object instanceof Restaurant){
                $class = 'restaurant';
                $object->setImage($this->cacheManager->getBrowserPath('uploads/images/'.$class.'/'.$object->getImage(),'square'));
            }
        }else{
            if ($object instanceof Restaurant){
                $object->setImage($this->cacheManager->getBrowserPath('La_demarche_Dabba.png','square'));
            }
        }

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Cms or $data instanceof Restaurant;
    }
}