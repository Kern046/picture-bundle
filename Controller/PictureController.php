<?php

namespace Kern\PictureBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Kern\PictureBundle\Manager\PictureManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PictureController extends AbstractController
{
    /**
     * @Route("/pictures", name="create_picture", methods={"POST"})
     */
    public function createPicture(Request $request, PictureManager $pictureManager)
    {
        return new JsonResponse($pictureManager->create(
            $request->files->get('file'),
            $this->getUser(),
            $request->query->get('credits')
        ), 201);
    }

    /**
     * @Route("/pictures/{hash}", name="get_picture", methods={"GET"})
     */
    public function getPicture(string $hash, PictureManager $pictureManager)
    {
        if (($picture = $pictureManager->find($hash)) === null) {
            throw new NotFoundHttpException('pictures.not_found');
        }
        return new JsonResponse($picture);
    }

    /** 
     * @Route("/pictures/{hash}/{format}", name="get_formatted_picture", methods={"GET"})
     */
    public function getFormattedPicture(string $hash, int $format, PictureManager $pictureManager)
    {
        if (($picture = $pictureManager->find($hash)) === null) {
            throw new NotFoundHttpException('pictures.not_found');
        }
        if (!$picture->hasFormat($format)) {
            throw new BadRequestHttpException('pictures.invalid_format');
        }
        $response = new BinaryFileResponse("{$this->getParameter('upload_dir')}/{$picture->getHash()}/{$format}px.{$picture->getExtension()}");
        $response->headers->set('Content-Type', $picture->getMimeType());
        return $response;
    }
}