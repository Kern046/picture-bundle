<?php

namespace Kern\PictureBundle\EventListener;

use Kern\PictureBundle\Entity\Picture;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class PictureUploaderMapper
{
    /** @var string */
    protected $userClass;

    public function __construct(string $userClass)
    {
        $this->userClass = $userClass;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        $metadata = $event->getClassMetadata();

        if ($metadata->getName() !== Picture::class) {
            return;
        }
        $metadata->mapManyToOne([
            'targetEntity' => $this->userClass,
            'fieldName' => 'uploader',
            'cascade' => [],
            'joinColumn' => [
                'nullable' => true
            ]
        ]);
    }
}