<?php

namespace Kern\PictureBundle\EventListener;

use Kern\PictureBundle\Manager\PictureManager;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class PictureListener
{
    /** @var PictureManager */
    protected $pictureManager;

    public function __construct(PictureManager $pictureManager)
    {
        $this->pictureManager = $pictureManager;
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        $this->pictureManager->processUploadedPictures();
    }
}