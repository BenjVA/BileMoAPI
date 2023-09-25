<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Hateoas\Configuration\Route as HateoasRoute;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\Factory\PagerfantaFactory;
use JMS\Serializer\SerializerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Pagerfanta\Pagerfanta;

class UserController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route('/bilemo/users', name: 'app_users', methods: ['GET'])]
    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour consulter les utilisateurs')]
    public function getAllUsers(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $adapter = new ArrayAdapter($userRepository->findAll());
        $pager = new Pagerfanta($adapter);
        $pagerFanta = new PagerfantaFactory();
        $idCache = 'getAllUsers-';

        $usersPaginated = $pagerFanta->createRepresentation(
            $pager,
            new HateoasRoute('app_users', array(), true),
            new CollectionRepresentation($pager->getCurrentPageResults())
        );

        $jsonUserList = $cache->get(
            $idCache,
            function (ItemInterface $item) use ($serializer, $usersPaginated) {
                echo('L\'élément n\'est pas encore en cache !');
                $item->tag('usersCache')
                    ->expiresAfter(60);
                $userList
                    = $usersPaginated;


                return $serializer->serialize($userList, 'json');
            }
        );

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/bilemo/users/{id}', name: 'app_users_details', methods: ['GET'])]
    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour consulter un utilisateur')]
    public function getUserDetails(
        User $user,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsonUser = $serializer->serialize($user, 'json');

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }
}
