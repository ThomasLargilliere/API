<?php

namespace App\Service;

use Hateoas\HateoasBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class HateoasJsonResponse
{
    public function getHateoasJsonResponse($items, $codeStatus)
    {
        $hateoas = HateoasBuilder::create()->build();
        $json = $hateoas->serialize($items, 'json');
        $response = new JsonResponse();
        $response->setContent($json);
        $response->setStatusCode($codeStatus);
        return $response;
    }
}

?>