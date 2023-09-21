<?php

namespace App\Controller;

use App\Repository\UserRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UserController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route('/bilemo/users', name: 'app_users', methods: ['GET'])]
    public function getAllUsers(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $idCache = 'getAllUsers-';

        $jsonUserList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $serializer) {
            echo ('L\'élément n\'est pas encore en cache !');
            $item->tag('usersCache')
                ->expiresAfter(60);
            $userList = $userRepository->findAll(); // pagination à faire avec futur bundle
            $context = SerializationContext::create()->setGroups(['getAllUsers']);

            return $serializer->serialize($userList, 'json', $context);
        });

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }
}
