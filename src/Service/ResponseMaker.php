<?php

namespace Deozza\ResponseMakerBundle\Service;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseMaker
{
    const NOT_ALLOWED = 405;
    const NOT_ALLOWED_MESSAGE = "The method %s is not allowed on this route.";

    const BAD_REQUEST = 400;
    const NOT_FOUND = 404;
    const NOT_AUTHORIZED = 401;
    const FORBIDDEN_ACCESS = 403;
    const CONFLICT = 409;
    const CREATED = 201;
    const OK = 200;
    const EMPTY = 204;
    const CONTENT_TYPE = ['Content-Type'=>"application/json"];

    public function __construct(SerializerInterface $serializer, FormErrorSerializer $formErrorSerializer)
    {
        $this->response = new JsonResponse();
        $this->response->headers->add(self::CONTENT_TYPE);
        $this->serializer = $serializer;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    public function methodNotAllowed(string $method)
    {
        $this->response->setStatusCode(self::NOT_ALLOWED);
        $this->response->setContent(json_encode(["error"=>sprintf(self::NOT_ALLOWED_MESSAGE, $method)]));
        return $this->response;
    }

    public function badRequest($message)
    {
        $this->response->setStatusCode(self::BAD_REQUEST);
        $this->response->setContent(json_encode(["error"=>$message]));
        return $this->response;
    }

    public function badForm($form)
    {
        return $this->badRequest($this->formErrorSerializer->convertFormToArray($form));
    }

    public function notFound(string $message)
    {
        $this->response->setStatusCode(self::NOT_FOUND);
        $this->response->setContent(
            json_encode(
                [
                    "error" => $message
                ]
            )
        );
        return $this->response;
    }

    public function notAuthorized()
    {
        $this->response->setStatusCode(self::NOT_AUTHORIZED);
        return $this->response;
    }

    public function forbiddenAccess(string $message = null)
    {
        $this->response->setStatusCode(self::FORBIDDEN_ACCESS);
        $this->response->setContent(
            json_encode(
                [
                    "error" => $message
                ]
            )
        );
        return $this->response;
    }

    public function conflict($message, $context, $serializerGroups = ['Default'])
    {
        $this->response->setStatusCode(self::CONFLICT);
        $serializedContext = $this->serializer->serialize($context, 'json', SerializationContext::create()->setGroups($serializerGroups));


        $this->response->setContent(
            json_encode(
                ["conflicts" => $message, "context"=>json_decode($serializedContext)]

            )
        );
        return $this->response;
    }

    public function created($item, $serializerGroups = ['Default'])
    {
        $this->response->setStatusCode(self::CREATED);
        $serialized = $this->serializer->serialize($item, 'json', SerializationContext::create()->setGroups($serializerGroups));
        $this->response->setContent($serialized);
        return $this->response;
    }

    public function ok($item = null, $serializerGroups = ['Default'])
    {
        $this->response->setStatusCode(self::OK);
        $serialized = $this->serializer->serialize($item, 'json', SerializationContext::create()->setGroups($serializerGroups));
        $this->response->setContent($serialized);
        return $this->response;
    }

    public function okPaginated(array $item = null, $serializerGroups = ['Default'], int $count, int $page)
    {
        $this->response->setStatusCode(self::OK);
        $content = [
            "current_page_number"=>$page,
            "num_items_per_page"=>$count,
            "items"=>$item
        ];
        $serialied = $this->serializer->serialize($content, 'json', SerializationContext::create()->setGroups($serializerGroups));
        $this->response->setContent($serialied);
        return $this->response;
    }
}