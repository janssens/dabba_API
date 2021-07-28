<?php
// src/Serializer/ImageNormalizer.php

namespace App\Serializer;

use App\Entity\Cms;
use App\Entity\Restaurant;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ImageNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'IMAGE_NORMALIZER_ALREADY_CALLED';

    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        if ($object->getImage()){
            $class = 'default';
            if ($object instanceof Cms){
                $class = 'cms';
            }
            if ($object instanceof Restaurant){
                $class = 'restaurant';
            }

            $object->setImage($this->router->generate('app_home',[],UrlGeneratorInterface::ABSOLUTE_URL).'uploads/images/'.$class.'/'.$object->getImage());
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