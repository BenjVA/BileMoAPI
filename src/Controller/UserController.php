<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hateoas\Configuration\Route as HateoasRoute;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\Factory\PagerfantaFactory;
use JMS\Serializer\SerializerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        $idCache = 'getAllUsers-';

        $jsonUserList = $cache->get(
            $idCache,
            function (ItemInterface $item) use ($pager, $serializer) {
                $pagerFanta = new PagerfantaFactory();
                echo('L\'élément n\'est pas encore en cache !');
                $item->tag('usersCache')
                    ->expiresAfter(60);
                $userList
                    = $pagerFanta->createRepresentation(
                        $pager,
                        new HateoasRoute('app_users', array(), true),
                        new CollectionRepresentation($pager->getCurrentPageResults())
                    );

                return $serializer->serialize($userList, 'json');
            }
        );

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/bilemo/users/{id}', name: 'app_users_details', methods: ['GET'])]
    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour consulter un utilisateur')]
    public function getUserDetails(
        User $user,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonUser = $serializer->serialize($user, 'json');

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/bilemo/users/create', name: 'app_users_create', methods: ['POST'])]
    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour créer un utilisateur')]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        CustomerRepository $customerRepository
    ): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        /*dd($request->query->get('user'));
        $idCustomer = $request->query->get();
        $user->setCustomer($customerRepository->find($idCustomer));*/

        $entityManager->persist($user);
        $entityManager->flush();

        $jsonUser = $serializer->serialize($user, 'json');

        $location = $urlGenerator->generate(
            'app_users_details',
            ['id' => $user->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['Location' => $location], true);
    }
}
